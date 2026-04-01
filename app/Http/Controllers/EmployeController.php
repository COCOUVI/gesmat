<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EquipementEtat;
use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\EquipementDemandé;
use App\Models\Panne;
use App\Models\User;
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

        $nbr_non_resolue = Panne::where('user_id', $user->id)
            ->where('statut', '!=', 'resolu')
            ->count();

        $nbr_assign = Affectation::where('user_id', $user->id)->count();

        $affectations = Affectation::with('equipement')
            ->where('user_id', $user->id)
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
        $equipements_par_categorie = Categorie::with('equipements')->get();
        $user = Auth::user();

        return view('employee.layouts.askpage', compact('user', 'equipements_par_categorie'));
    }

    public function SubmitAsk(Request $request)
    {
        // Validation via Form Request
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $demande = new Demande();
            $demande->lieu = $request->lieu;
            $demande->motif = $request->motif;
            $demande->user_id = $user->id;
            $demande->statut = 'en_attente';
            $demande->save();
            $equipements = $request->equipements;
            $quantity = $request->quantites;
            foreach ($equipements as $index => $equipement_id) {
                $qte = $quantity[$index];
                $equipements_ask = new EquipementDemandé();
                $equipements_ask->demande_id = $demande->id;
                $equipements_ask->equipement_id = $equipement_id;
                $equipements_ask->nbr_equipement = $qte;
                $equipements_ask->save();
            }
            DB::Commit();

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
        $equipements_user = Affectation::where('user_id', $user->id)
            ->with('equipement')
            ->get()
            ->pluck('equipement');

        return view('employee.layouts.panne', compact('user', 'equipements_user'));
    }

    /**
     * Traite le signalement de panne d'équipement
     */
    public function HandlePanne(Request $request)
    {
        $validated = $request->validated();
        try {
            DB::beginTransaction();
            $user = Auth::user();
            // Créer la panne
            Panne::create([
                'equipement_id' => $validated['equipement_id'],
                'user_id' => $user->id,
                'description' => $validated['description'],
                'statut' => 'en_cours',
            ]);
            // Mettre à jour l'état de l'équipement
            $equipement = Equipement::find($validated['equipement_id']);
            if ($equipement) {
                $equipement->update(['etat' => EquipementEtat::EN_PANNE->value]);
            }
            DB::commit();

            return back()->with('success', 'Panne signalée avec succès.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du signalement de panne: '.$e->getMessage());

            return back()->with('error', 'Une erreur est survenue lors du signalement de la panne.');
        }
    }

    /**
     * Affiche les équipements assignés à l'utilisateur
     */
    public function equipementsAssignes()
    {
        $user = Auth::user();
        $affectation = Affectation::with('equipement')
            ->where('user_id', $user->id)
            ->paginate(4);

        return view('employee.layouts.assign', compact('user', 'affectation'));
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
     * Affiche la liste paginée des pannes
     */
    public function ShowPannes()
    {
        $user = Auth::user();
        $pannes = Panne::with('equipement')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(5);

        return view('employee.layouts.pannelist', compact('pannes', 'user'));
    }

    /**
     * Affiche la liste paginée des demandes
     */
    public function ShowDemandes()
    {
        $user = Auth::user();
        $demandes = Demande::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(5);

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
