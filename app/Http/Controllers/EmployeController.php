<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ReportPanneAction;
use App\Actions\SubmitDemandeAction;
use App\Mail\HelpRequestMail;
use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Demande;
use App\Models\Panne;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * EmployeController gère les actions des employés
 * - Dashboard et statistiques
 * - Demandes d'équipement
 * - Signalement de pannes
 * - Gestion des équipements assignés
 * - Demande d'aide
 */
final class EmployeController extends Controller
{
    public function __construct(
        private readonly SubmitDemandeAction $submitDemandeAction,
        private readonly ReportPanneAction $reportPanneAction,
    ) {}

    /**
     * Affiche le tableau de bord principal avec les statistiques
     */
    public function index()
    {
        $user = Auth::user();

        // Récupération optimisée des statistiques
        $demandes = Demande::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        $nbr_accept = Demande::where('user_id', $user->id)
            ->where('statut', 'acceptee')
            ->count();

        $nbr_en_attente = Demande::where('user_id', $user->id)
            ->where('statut', 'en_attente')
            ->count();

        $nbr_non_resolue = (int) Panne::with(['equipement', 'affectation'])
            ->where('user_id', $user->id)
            ->where('statut', '!=', 'resolu')
            ->get()
            ->sum(fn (Panne $panne) => $panne->getQuantiteNonResolue());

        $nbr_assign = (int) Affectation::with('pannes')
            ->where('user_id', $user->id)
            ->get()
            ->sum(fn (Affectation $affectation) => $affectation->getQuantiteActive());

        $affectations = Affectation::with(['equipement', 'equipement.categorie'])
            ->where('user_id', $user->id)
            ->active()
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        $pannes = Panne::with('equipement')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(2)
            ->get();

        return view('employee.layouts.main', compact(
            'user',
            'demandes',
            'affectations',
            'pannes',
            'nbr_accept',
            'nbr_en_attente',
            'nbr_non_resolue',
            'nbr_assign'
        ));
    }

    /**
     * Affiche la page de création de demande d'équipement
     */
    public function ShowAskpage()
    {
        // Charger les catégories avec UNIQUEMENT les équipements en stock (quantite > 0)
        $equipements_par_categorie = Categorie::with([
            'equipements' => function ($query) {
                $query->withStock();
            },
        ])->get();

        $user = Auth::user();

        return view('employee.layouts.askpage', compact('user', 'equipements_par_categorie'));
    }

    public function SubmitAsk(Request $request)
    {
        $validated = $request->validate([
            'lieu' => 'required|string|max:255',
            'motif' => 'required|string|min:3|max:2000',
            'equipements' => 'required|array|min:1',
            'equipements.*' => 'required|integer|exists:equipements,id',
            'quantites' => 'required|array|min:1',
            'quantites.*' => 'required|integer|min:1',
        ], [
            'equipements.required' => 'Veuillez sélectionner au moins un équipement.',
            'quantites.required' => 'Veuillez indiquer la quantité demandée.',
        ]);

        try {
            $this->submitDemandeAction->handle(Auth::user(), $validated);

            return back()->with('success', 'Demande envoyé avec succès');
        } catch (Throwable $e) {
            Log::error('Erreur lors de la soumission de la demande : '.$e->getMessage());

            return back()->with('error', 'Une erreur est survenue lors de l’envoi de la demande.');
        }
    }

    /**
     * Affiche la page de signalement de panne
     */
    public function signalerPanne()
    {
        $user = Auth::user();

        // Récupérer les affectations actives avec leurs équipements et pannes non résolues
        $affectations = Affectation::where('user_id', $user->id)
            ->active()
            ->with([
                'equipement',
                'pannes' => function ($query) {
                    $query->where('statut', '!=', 'resolu');
                },
            ])
            ->get();

        $affectations = $affectations
            ->filter(fn (Affectation $affectation) => $affectation->getQuantiteDisponiblePourPanne() > 0)
            ->values();

        return view('employee.layouts.panne', compact('user', 'affectations'));
    }

    /**
     * Traite le signalement de panne d'équipement
     */
    /**
     * Signale une panne d'équipement
     * Valide d'abord que l'employé a reçu cet équipement et pas déjà signalé tout
     */
    public function HandlePanne(Request $request)
    {
        $validated = $request->validate([
            'affectation_id' => 'required|integer|exists:affectations,id',
            'quantite' => 'required|integer|min:1',
            'description' => 'required|string|min:10|max:1000',
        ], [
            'affectation_id.required' => 'Affectation requise',
            'affectation_id.exists' => 'Affectation inexistante',
            'quantite.required' => 'Quantité requise',
            'quantite.min' => 'Quantité minimale : 1',
            'quantite.integer' => 'La quantité doit être un nombre',
            'description.required' => 'Description requise',
            'description.min' => 'Description minimum 10 caractères',
            'description.max' => 'Description maximum 1000 caractères',
        ]);

        try {
            $this->reportPanneAction->handle(Auth::user(), $validated);

            return back()->with('success', sprintf(
                '%d équipement(s) marqué(es) en panne et signalé(e)s avec succès.',
                $validated['quantite']
            ));
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Erreur signalement panne employé: '.$e->getMessage());

            return back()->withErrors([
                'description' => 'Erreur lors du signalement de la panne. Veuillez réessayer.',
            ])->withInput();
        }
    }

    /**
     * Affiche les équipements assignés à l'utilisateur
     */
    public function equipementsAssignes()
    {
        $user = Auth::user();
        $affectations = Affectation::with([
            'equipement',
            'demande',
            'pannes' => function ($query) {
                $query->where('statut', '!=', 'resolu');
            },
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('employee.layouts.assign', compact('user', 'affectations'));
    }

    /**
     * Affiche la page d'aide
     */
    public function Helppage()
    {
        $user = Auth::user();

        return view('employee.layouts.help', compact('user'));
    }

    /**
     * Traite la soumission d'une demande d'aide
     */
    public function HandleHelp(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:10|max:2000',
        ], [
            'message.required' => 'Le message est requis',
            'message.min' => 'Le message doit contenir au moins 10 caractères',
        ]);

        try {
            $adminEmail = config('mail.from.address', 'admin@gesmat.local');
            $mailable = new HelpRequestMail(Auth::user()->email, $validated['message']);
            $mailable->afterCommit();
            Mail::to($adminEmail)->queue($mailable);

            return back()->with('success', 'Votre message a été envoyé à l\'administrateur.');
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'envoi d\'aide: '.$e->getMessage());

            return back()->with('error', 'Une erreur est survenue lors de l\'envoi du message.');
        }
    }

    /**
     * Supprime un signalement de panne
     */
    public function DeletePanne(Panne $panne)
    {
        $this->authorizeDelete($panne, Auth::user());

        try {
            $panne->delete();

            return back()->with('success', 'Panne supprimée avec succès.');
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression de panne: '.$e->getMessage());

            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    /**
     * Supprime une affectation d'équipement
     */
    public function DeleteAffect(Affectation $affectation)
    {
        $this->authorizeDelete($affectation, Auth::user());

        try {
            $affectation->delete();

            return back()->with('success', 'Affectation supprimée avec succès.');
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression d\'affectation: '.$e->getMessage());

            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    /**
     * Supprime une demande d'équipement
     */
    public function DeleteAsk(Demande $demande)
    {
        $this->authorizeDelete($demande, Auth::user());

        try {
            $demande->delete();

            return back()->with('success', 'Demande supprimée avec succès.');
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression de demande: '.$e->getMessage());

            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    /**
     * Affiche la liste des pannes
     */
    public function ShowPannes()
    {
        $user = Auth::user();
        $pannes = Panne::with('equipement')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('employee.layouts.pannelist', compact('pannes', 'user'));
    }

    /**
     * Affiche la liste des demandes
     */
    public function ShowDemandes()
    {
        $user = Auth::user();
        $demandes = Demande::with(['equipements', 'affectations'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('employee.layouts.list_demandes', compact('user', 'demandes'));
    }

    /**
     * Vérifie l'autorisation pour supprimer une ressource
     *
     * @param  mixed  $model
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function authorizeDelete($model, User $user): void
    {
        if ($model->user_id !== $user->id) {
            abort(403, 'Non autorisé à supprimer cette ressource.');
        }
    }
}
