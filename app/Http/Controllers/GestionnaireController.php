<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\CollaborateurExterne;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * GestionnaireController - Gère les opérations au niveau gestionnaire
 *
 * Responsabilités:
 * - Gestion des équipements perdus/retournés
 * - Gestion des pannes d'équipements
 * - Gestion des bons d'entrée/sortie (collaborateurs externes)
 * - Gestion des affectations d'équipements
 * - Gestion des collaborateurs externes
 * - Traitement des demandes d'équipements
 */
final class GestionnaireController extends Controller
{
    /**
     * Affiche la liste des équipements perdus (non retournés à la date)
     */
    public function equipementsPerdus()
    {
        $equipement_lost = Affectation::with(['equipement', 'user'])
            ->whereDate('date_retour', '<', Carbon::today())
            ->get();

        return view('gestionnaire.tools.lost_tools', compact('equipement_lost'));
    }

    /**
     * Marque un équipement comme retourné sans générer de PDF
     */
    public function retournerEquipement($id)
    {
        try {
            $affectation = Affectation::with('equipement')->findOrFail($id);

            // Marquer l'affectation comme retournée
            $affectation->update(['date_retour' => now()]);

            return redirect()->route('gestionnaire.equipements.perdus')
                ->with('success', 'Équipement marqué comme retourné.');
        } catch (Exception $e) {
            Log::error("Erreur lors du retour d'équipement: ".$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors du traitement.');
        }
    }

    /**
     * Affiche le dashboard du gestionnaire avec statistiques globales
     */
    public function dashboard()
    {
        $totalEquipements = (int) Equipement::sum('quantite');
        $equipementsAffectes = (int) Affectation::with('pannes')
            ->get()
            ->sum(fn (Affectation $affectation) => $affectation->getQuantiteActive());
        $equipementsEnPanne = (int) Panne::with(['equipement', 'affectation'])
            ->where('statut', '!=', 'resolu')
            ->get()
            ->sum(fn (Panne $panne) => $panne->getQuantiteNonResolue());
        $utilisateursActifs = User::count();

        return view('gestionnaire.homedash', compact(
            'totalEquipements',
            'equipementsAffectes',
            'equipementsEnPanne',
            'utilisateursActifs'
        ));
    }

    // ======================================== PANNES ========================================

    /**
     * Affiche la liste des pannes en attente
     */
    public function voirPannes()
    {
        $pannes = Panne::where('statut', 'en_attente')
            ->with(['equipement', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('gestionnaire.pannes.index', compact('pannes'));
    }

    /**
     * Marque une panne comme résolue
     */
    /**
     * Résout une panne en la marquant comme résolue
     */
    public function PutPanne(Panne $panne)
    {
        try {
            DB::beginTransaction();

            $panne->update(['statut' => 'resolu']);

            Log::info("Panne #{$panne->id} résolue par gestionnaire", [
                'equipement_id' => $panne->equipement_id,
                'quantite' => $panne->quantite,
            ]);

            DB::commit();

            return redirect()->back()->with('success', sprintf(
                '%d équipement(s) marqué(s) comme réparé(s).',
                $panne->quantite
            ));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur résolution panne gestionnaire: '.$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors du traitement. Veuillez réessayer.');
        }
    }

    // ======================================== BONS ========================================

    /**
     * Affiche le formulaire de création de bon pour collaborateurs externes
     */
    public function showBonForm()
    {
        $collaborateurs = CollaborateurExterne::all();

        return view('gestionnaire.bons.bon_external_collaborator', compact('collaborateurs'));
    }

    /**
     * Traite la soumission du formulaire de bon
     */
    public function handleBon(Request $request)
    {
        $validated = $request->validate([
            'collaborateur_id' => 'required|exists:collaborateur_externes,id',
            'motif' => 'required|string|max:500',
            'type' => 'required|in:entrée,sortie',
        ]);

        try {
            DB::beginTransaction();

            $bon = Bon::create([
                'user_id' => $validated['collaborateur_id'],
                'motif' => $validated['motif'],
                'statut' => $validated['type'],
            ]);

            $pdfPath = $this->generateBonPdfFile($bon);

            DB::commit();

            return view('gestionnaire.bons.bon_external_collaborator', [
                'collaborateurs' => CollaborateurExterne::all(),
            ])->with('success', 'Bon généré avec succès.')
                ->with('pdf', asset('storage/'.$pdfPath));

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la génération du bon: '.$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors de la génération du bon.');
        }
    }

    // ======================================== RETOUR D'ÉQUIPEMENT ========================================

    /**
     * Traite le retour d'équipement affecté et génère un PDF de confirmation
     */
    public function BackTool(Affectation $affectation)
    {
        try {
            DB::beginTransaction();

            $affectation->update(['date_retour' => now()]);

            $equipement = $affectation->equipement;
            $user = $affectation->user;

            if ($equipement) {
                $equipement->update([
                    'quantite' => $equipement->quantite + $affectation->quantite_affectee,
                ]);
            }

            $pdfPath = $this->generateReturnPdf($equipement, $user);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Retour du matériel effectué. Un PDF de confirmation a été généré.')
                ->with('pdf', asset('storage/'.$pdfPath));

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors du retour d'équipement: ".$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors du traitement.');
        }
    }

    // ======================================== COLLABORATEURS EXTERNES ========================================

    /**
     * Affiche le formulaire de création d'un collaborateur externe
     */
    public function createCollaborateur()
    {
        return view('gestionnaire.collaborateurs.collaborator_external');
    }

    /**
     * Enregistre un collaborateur externe avec sa carte d'identité
     */
    public function storeCollaborateur(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'chemin_carte' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        try {
            $chemin = $request->file('chemin_carte')->store('cartes_identite', 'public');

            CollaborateurExterne::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'carte_chemin' => $chemin,
            ]);

            return redirect()->route('gestionnaire.collaborateurs.index')
                ->with('success', 'Collaborateur ajouté avec succès.');
        } catch (Exception $e) {
            Log::error("Erreur lors de l'ajout du collaborateur: ".$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors de l\'ajout du collaborateur.');
        }
    }

    /**
     * Affiche la liste des collaborateurs externes
     */
    public function listCollaborateurs()
    {
        $collaborateurs = CollaborateurExterne::all();

        return view('gestionnaire.collaborateurs.list_collaborator', compact('collaborateurs'));
    }

    /**
     * Supprime un collaborateur externe et ses fichiers associés
     */
    public function destroyCollaborateur($id)
    {
        try {
            $collaborateur = CollaborateurExterne::findOrFail($id);

            if ($collaborateur->carte_chemin && Storage::disk('public')->exists($collaborateur->carte_chemin)) {
                Storage::disk('public')->delete($collaborateur->carte_chemin);
            }

            $collaborateur->delete();

            return redirect()->route('gestionnaire.collaborateurs.index')
                ->with('success', 'Collaborateur supprimé avec succès.');
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression du collaborateur: '.$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }
    // ======================================== DEMANDES D'ÉQUIPEMENTS ========================================

    /**
     * Affiche la liste des demandes en attente
     */
    public function ShowAllAsk()
    {
        $demandes = Demande::with('equipements')
            ->where('statut', 'en_attente')
            ->latest()
            ->get();

        return view('gestionnaire.demandes.liste', compact('demandes'));
    }

    /**
     * Assigne une demande à un gestionnaire
     */
    public function assignerDemande(Request $request, Demande $demande)
    {
        $validated = $request->validate([
            'gestionnaire_id' => 'required|exists:users,id',
        ]);

        try {
            $demande->update([
                'statut' => 'assignée',
                'gestionnaire_id' => $validated['gestionnaire_id'],
            ]);

            return back()->with('success', 'Demande assignée avec succès.');
        } catch (Exception $e) {
            Log::error("Erreur lors de l'assignation de demande: ".$e->getMessage());

            return back()->with('error', 'Erreur lors de l\'assignation.');
        }
    }

    /**
     * Accepte une demande et assigne automatiquement les équipements à l'employé
     */
    public function CheckAsk(Demande $demande)
    {
        try {
            DB::beginTransaction();

            // Assigner automatiquement les équipements
            $pdfPath = $this->assignEquipmentsFromDemande($demande);

            // Mettre à jour le statut de la demande
            $demande->update(['statut' => 'acceptee']);

            DB::commit();

            $message = 'La demande a été validée et les équipements ont été assignés automatiquement.';
            if ($pdfPath) {
                return redirect()->route('gestionnaire.demandes.list')
                    ->with('success', $message)
                    ->with('pdf', asset('storage/'.$pdfPath));
            }

            return redirect()->route('gestionnaire.demandes.list')->with('success', $message);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'acceptation de demande: ".$e->getMessage());

            return redirect()->route('gestionnaire.demandes.list')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Rejette une demande d'équipement
     */
    public function CancelAsk(Demande $demande)
    {
        try {
            $demande->update(['statut' => 'rejetee']);

            return redirect()->route('gestionnaire.demandes.list')
                ->with('success', 'Demande rejetée.');
        } catch (Exception $e) {
            Log::error('Erreur lors du rejet de demande: '.$e->getMessage());

            return redirect()->route('gestionnaire.demandes.list')
                ->with('error', 'Erreur lors du traitement.');
        }
    }

    /**
     * Met une demande en attente
     */
    public function LoadingAsk(Demande $demande)
    {
        try {
            $demande->update(['statut' => 'en_attente']);

            return redirect()->route('gestionnaire.demandes.list')
                ->with('success', 'Demande mise en attente.');
        } catch (Exception $e) {
            Log::error('Erreur lors de la mise en attente: '.$e->getMessage());

            return redirect()->route('gestionnaire.demandes.list')
                ->with('error', 'Erreur lors du traitement.');
        }
    }

    // ------------------------------------------------------------------------------------------------------------
    //   {
    //     DB::beginTransaction(); // start transaction

    //     try {
    //       //Enregistrer les affectations d'équipements
    //       foreach ($request->equipements as $index => $equipement_id) {
    //         $affectation = new Affectation();
    //         $affectation->equipement_id = $equipement_id;
    //         $affectation->date_retour = $request->dates_retour[$index];
    //         $affectation->user_id = $request->employe_id;
    //         $equipementChange = Equipement::where("id", "=", $equipement_id)->first();
    //         $equipementChange->etat = "usagé";
    //         $equipementChange->save();
    //         $affectation->save();
    //       }

    //       //
    //       $bon = new Bon();
    //       $bon->user_id = $request->employe_id;
    //       $bon->motif = $request->motif;
    //       $bon->statut = "sortie";
    //       $bon->save();

    //       DB::commit();
    //       return redirect()->back()->with("success", "Affectation réussie avec succès");
    //      } catch (\Exception $e) {
    //       DB::rollBack(); //
    //       return redirect()->back()->with("error", "Une erreur est survenue : " . $e->getMessage());
    //      }
    //     }

    //   {
    //     set_time_limit(120);
    //     DB::beginTransaction();
    //     $user = Auth::user();

    //     try {
    //       // Charger les équipements en bulk
    //       $equipementIds = $request->equipements;
    //       $equipements = Equipement::whereIn('id', $equipementIds)->get()->keyBy('id');
    //       $affectationsDetails = [];
    //       foreach ($request->equipements as $index => $equipement_id) {
    //         $quantite = $request->quantites[$index] ?? 1;
    //         $rawDate = $request->dates_retour[$index] ?? null;

    //         $equipement = $equipements->get($equipement_id);

    //         // if (!$equipement) {
    //         //   throw new \Exception("Équipement ID $equipement_id introuvable.");
    //         // }

    //         // if ($equipement->quantite < $quantite) {
    //         //   throw new \Exception("Quantité insuffisante pour l'équipement « {$equipement->nom} » (disponible : {$equipement->quantite}, demandée : {$quantite}).");
    //         // }

    // // ----------------------------------------------------------------------------------------------------------------
    // // ---------------------------------------autres essais pour rendre jolie le texte d'erreur---------------------------
    //         try {
    //             if (!$equipement) {
    //                 throw new \Exception("❗ ERREUR : ÉQUIPEMENT ID $equipement_id INTRROUVABLE !");
    //             }

    //             if ($equipement->quantite < $quantite) {
    //                 throw new \Exception("⚠️ QUANTITÉ INSUFFISANTE pour l’équipement « {$equipement->nom} » (DISPONIBLE : {$equipement->quantite}, DEMANDÉE : {$quantite}).");
    //             }

    //             // Le reste du traitement ici...

    //         } catch (\Exception $e) {
    //             return redirect()->back()->with('error', $e->getMessage());
    //         }

    //         $affectation = new Affectation();
    //         $affectation->equipement_id = $equipement_id;
    //         $affectation->user_id = $request->employe_id;
    //         $affectation->date_retour = $rawDate ?: null;
    //         $affectation->created_by = $user->nom . ' ' . $user->prenom;
    //         $affectation->quantite_affectee = $quantite;
    //         $affectation->save();

    //         $equipement->quantite -= $quantite;
    //         $equipement->etat = ($equipement->quantite > 0) ? "disponible" : "usagé";
    //         $equipement->save();
    //         $affectationsDetails[] = [
    //           'nom' => $equipement->nom,
    //           'reference' => $equipement->reference ?? '',
    //           'quantite' => $quantite,
    //         ];
    //       }

    //       $bon = new Bon();
    //       $bon->user_id = $request->employe_id;
    //       $bon->motif = $request->motif;
    //       $bon->statut = "sortie";
    //       $pdfName = 'bon_sortie_' . $request->employe_id . '_' . now()->timestamp . '.pdf';
    //       $pdfPath = 'bon_sortie/' . $pdfName;
    //       $bon->fichier_pdf = $pdfPath;
    //       $bon->save();

    //       DB::commit();

    //       $employe = User::find($request->employe_id);

    //       $pdf = Pdf::loadView('pdf.bon', [
    //         'date' => now()->format('d/m/Y'),
    //         'nom' => $employe->nom ?? 'Admin',
    //         'prenom' => $employe->prenom ?? '',
    //         'motif' => $request->motif,
    //         'numero_bon' => $bon->id,
    //         'type' => $bon->statut,
    //         'equipements' => $affectationsDetails,
    //       ]);

    //       Storage::disk('public')->put($pdfPath, $pdf->output());

    //       return redirect()->back()
    //         ->with('success', 'Affectation réussie avec succès et un bon de sortie a été généré.')
    //         ->with('pdf', asset('storage/' . $pdfPath));
    //     } catch (\Exception $e) {
    //       DB::rollBack();
    //       Log::error("Erreur lors de l'affectation : " . $e->getMessage());
    //       return redirect()->back()->with("error", $e->getMessage());
    //     }
    //   }

    public function handleAffectation(Request $request)
    {
        $validated = $request->validate([
            'equipements' => 'required|array',
            'quantites' => 'required|array',
            'employe_id' => 'required|exists:users,id',
            'motif' => 'required|string|max:500',
        ], [
            'equipements.required' => 'Le champ équipement est requis.',
            'quantites.required' => 'Le champ quantité est requis.',
        ]);

        set_time_limit(120);
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $equipementIds = $validated['equipements'];
            $equipements = Equipement::whereIn('id', $equipementIds)->get()->keyBy('id');
            $affectationsDetails = [];

            foreach ($validated['equipements'] as $index => $equipement_id) {
                $quantite = $validated['quantites'][$index] ?? 1;
                $rawDate = $request->dates_retour[$index] ?? null;

                $equipement = $equipements->get($equipement_id);

                if (! $equipement) {
                    throw new Exception("Équipement ID $equipement_id introuvable.");
                }

                if ($equipement->quantite < $quantite) {
                    throw new Exception(
                        "Quantité insuffisante pour l'équipement « {$equipement->nom} » ".
                        "(disponible : {$equipement->quantite}, demandée : {$quantite})."
                    );
                }

                // Créer l'affectation
                $affectation = Affectation::create([
                    'equipement_id' => $equipement_id,
                    'user_id' => $validated['employe_id'],
                    'date_retour' => $rawDate ?: null,
                    'created_by' => $user->nom.' '.$user->prenom,
                    'quantite_affectee' => $quantite,
                ]);

                // Mettre à jour l'équipement
                $nouvelleQuantite = $equipement->quantite - $quantite;
                $equipement->update([
                    'quantite' => $nouvelleQuantite,
                ]);

                $affectationsDetails[] = [
                    'nom' => $equipement->nom,
                    'reference' => $equipement->reference ?? '',
                    'quantite' => $quantite,
                ];
            }

            // Créer le bon de sortie
            $bon = Bon::create([
                'user_id' => $validated['employe_id'],
                'motif' => $validated['motif'],
                'statut' => 'sortie',
                'fichier_pdf' => null,
            ]);

            $pdfPath = $this->generateAffectationBonPdf($bon, $affectationsDetails, $validated['employe_id']);
            $bon->update(['fichier_pdf' => $pdfPath]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Affectation réussie avec succès et un bon de sortie a été généré.')
                ->with('pdf', asset('storage/'.$pdfPath));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'affectation: ".$e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ======================================== MÉTHODES PRIVÉES ========================================

    /**
     * Génère un PDF pour un bon de collaborateur externe
     */
    private function generateBonPdfFile(Bon $bon): string
    {
        $pdfName = 'bon_'.$bon->id.'_'.now()->timestamp.'.pdf';
        $pdfPath = 'pdf_bons/'.$pdfName;

        $pdf = Pdf::loadView('gestionnaire.bons.pdf_bon', compact('bon'));
        Storage::disk('public')->put($pdfPath, $pdf->output());

        return $pdfPath;
    }

    /**
     * Génère un PDF pour un retour d'équipement
     */
    private function generateReturnPdf(Equipement $equipement, User $user): string
    {
        $pdfName = 'retour_'.$equipement->id.'_'.now()->timestamp.'.pdf';
        $pdfPath = 'retour_perdu/'.$pdfName;

        $pdf = Pdf::loadView('pdf.retour_perdu', [
            'date' => now(),
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'equipement' => $equipement->nom,
        ]);

        Storage::disk('public')->put($pdfPath, $pdf->output());

        return $pdfPath;
    }

    /**
     * Génère un PDF pour un bon d'affectation
     */
    private function generateAffectationBonPdf(Bon $bon, array $affectationsDetails, int $employe_id): string
    {
        $pdfName = 'bon_sortie_'.$employe_id.'_'.now()->timestamp.'.pdf';
        $pdfPath = 'bon_sortie/'.$pdfName;

        $employe = User::find($employe_id);

        $pdf = Pdf::loadView('pdf.bon', [
            'date' => now()->format('d/m/Y'),
            'nom' => $employe->nom ?? 'Admin',
            'prenom' => $employe->prenom ?? '',
            'motif' => $bon->motif,
            'numero_bon' => $bon->id,
            'type' => $bon->statut,
            'equipements' => $affectationsDetails,
        ]);

        Storage::disk('public')->put($pdfPath, $pdf->output());

        return $pdfPath;
    }

    /**
     * Assigne automatiquement les équipements d'une demande à l'employé
     */
    private function assignEquipmentsFromDemande(Demande $demande): ?string
    {
        $user = Auth::user();
        $equipementsData = $demande->equipements()->get();

        if ($equipementsData->isEmpty()) {
            return null; // Pas d'équipements à assigner
        }

        $affectationsDetails = [];
        $employe_id = $demande->user_id;

        foreach ($equipementsData as $equipement) {
            $quantite = $equipement->pivot->nbr_equipement ?? 1;

            // Vérifier la disponibilité
            if (! $equipement->peutAffecter($quantite)) {
                throw new Exception(sprintf(
                    "Quantité insuffisante pour l'équipement « %s » (disponible : %d, demandée : %d).",
                    $equipement->nom,
                    $equipement->getQuantiteDisponible(),
                    $quantite
                ));
            }

            // Créer l'affectation
            Affectation::create([
                'equipement_id' => $equipement->id,
                'user_id' => $employe_id,
                'date_retour' => null,
                'created_by' => $user->nom.' '.$user->prenom,
                'quantite_affectee' => $quantite,
            ]);

            // Mettre à jour l'équipement
            $nouvelleQuantite = $equipement->quantite - $quantite;
            $equipement->update([
                'quantite' => $nouvelleQuantite,
            ]);

            $affectationsDetails[] = [
                'nom' => $equipement->nom,
                'reference' => $equipement->reference ?? '',
                'quantite' => $quantite,
            ];
        }

        // Créer le bon de sortie
        $pdfName = 'bon_sortie_demande_'.$demande->id.'_'.now()->timestamp.'.pdf';
        $pdfPath = 'bon_sortie/'.$pdfName;

        $employe = User::find($employe_id);
        $bon = Bon::create([
            'user_id' => $employe_id,
            'motif' => $demande->motif ?? 'Affectation automatique de demande',
            'statut' => 'sortie',
            'fichier_pdf' => $pdfPath,
        ]);

        $this->generateAffectationBonPdf($bon, $affectationsDetails, $employe_id);

        return $pdfPath;
    }
}
