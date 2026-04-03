<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Demande;
use App\Models\EquipementDemandé;
use App\Models\Panne;
use App\Models\User;
use App\Services\WorkflowNotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        private readonly WorkflowNotificationService $workflowNotificationService,
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
            DB::beginTransaction();
            $user = Auth::user();
            $demande = Demande::create([
                'lieu' => $validated['lieu'],
                'motif' => $validated['motif'],
                'user_id' => $user->id,
                'statut' => 'en_attente',
            ]);

            $quantitesParEquipement = [];

            foreach ($validated['equipements'] as $index => $equipementId) {
                $quantite = (int) ($validated['quantites'][$index] ?? 0);
                $quantitesParEquipement[$equipementId] = ($quantitesParEquipement[$equipementId] ?? 0) + $quantite;
            }

            foreach ($quantitesParEquipement as $equipementId => $quantite) {
                $equipements_ask = new EquipementDemandé();
                $equipements_ask->demande_id = $demande->id;
                $equipements_ask->equipement_id = $equipementId;
                $equipements_ask->nbr_equipement = $quantite;
                $equipements_ask->save();
            }

            DB::commit();

            $this->workflowNotificationService->notifyDemandeSubmitted(
                $demande->fresh(['user', 'equipements'])
            );

            return back()->with('success', 'Demande envoyé avec succès');
        } catch (Throwable $e) {
            DB::rollBack();
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
            DB::beginTransaction();
            $user = Auth::user();
            $affectation = Affectation::with('equipement')
                ->active()
                ->findOrFail($validated['affectation_id']);

            if ($affectation->user_id !== $user->id) {
                DB::rollBack();

                return back()->withErrors([
                    'affectation_id' => 'Vous ne pouvez signaler une panne que sur vos propres affectations.',
                ])->withInput();
            }

            $equipement = $affectation->equipement;
            $quantiteAffectee = $affectation->quantite_affectee;
            $quantiteEnPanneSignalee = $affectation->pannes()
                ->where('statut', '!=', 'resolu')
                ->sum('quantite');

            // Vérifier que la quantité à signaler ne dépasse pas ce qui reste
            $quantiteRestante = $quantiteAffectee - $quantiteEnPanneSignalee;

            if ($validated['quantite'] > $quantiteRestante) {
                DB::rollBack();

                return back()->withErrors([
                    'quantite' => sprintf(
                        'Vous ne pouvez signaler que %d équipement(s) en panne pour cette affectation (affecté: %d, déjà signalé: %d).',
                        $quantiteRestante,
                        $quantiteAffectee,
                        $quantiteEnPanneSignalee
                    ),
                ])->withInput();
            }

            // Créer la panne liée à l'affectation concernée
            $panne = Panne::create([
                'equipement_id' => $equipement->id,
                'affectation_id' => $affectation->id,
                'user_id' => $user->id,
                'quantite' => $validated['quantite'],
                'description' => $validated['description'],
                'statut' => 'en_attente',
            ]);

            DB::commit();

            $this->workflowNotificationService->notifyPanneReported(
                $panne->fresh(['user', 'equipement', 'affectation'])
            );

            return back()->with('success', sprintf(
                '%d équipement(s) marqué(es) en panne et signalé(e)s avec succès.',
                $validated['quantite']
            ));
        } catch (Exception $e) {
            DB::rollBack();
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

            Mail::send('emails.aide', [
                'email' => Auth::user()->email,
                'body' => $validated['message'],
            ], function ($mail) use ($adminEmail) {
                $mail->to($adminEmail)
                    ->subject('Demande d\'aide d\'un employé');
            });

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
