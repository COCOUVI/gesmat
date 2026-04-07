<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CancelAffectationAction;
use App\Actions\CreateDirectAffectationAction;
use App\Actions\CreateEquipementAction;
use App\Actions\CreateExternalCollaboratorAction;
use App\Actions\CreateExternalCollaboratorBonAction;
use App\Actions\RegisterEquipmentReturnAction;
use App\Actions\ReplacePanneEquipmentAction;
use App\Actions\ResolvePanneAction;
use App\Actions\ServeDemandeAction;
use App\Actions\StoreInternalPanneAction;
use App\Events\DemandeServed;
use App\Events\DirectAffectationCreated;
use App\Events\EquipmentReturned;
use App\Events\PanneReplacementCompleted;
use App\Events\PanneResolved;
use App\Http\Requests\EditRequest;
use App\Http\Requests\RegisterEquipmentReturnRequest;
use App\Http\Requests\ReplacePanneEquipmentRequest;
use App\Http\Requests\ResolvePanneRequest;
use App\Http\Requests\ServeDemandeRequest;
use App\Http\Requests\StoreDirectAffectationRequest;
use App\Http\Requests\StoreInternalPanneRequest;
use App\Http\Requests\UpdateEquipementRequest;
use App\Models\Affectation;
use App\Models\Bon;
use App\Models\Categorie;
use App\Models\CollaborateurExterne;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\Rapport;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * AdminController - Gère les opérations administratives du système
 *
 * Responsabilités:
 * - Gestion des utilisateurs et permissions
 * - Gestion des équipements (CRUD)
 * - Gestion des affectations d'équipements
 * - Gestion des demandes d'équipements
 * - Gestion des pannes d'équipements
 * - Gestion des collaborateurs externes
 * - Génération des bons d'entrée/sortie
 */
final class AdminController extends Controller
{
    public function __construct(
        private readonly ServeDemandeAction $serveDemandeAction,
        private readonly CreateDirectAffectationAction $createDirectAffectationAction,
        private readonly CreateEquipementAction $createEquipementAction,
        private readonly CreateExternalCollaboratorAction $createExternalCollaboratorAction,
        private readonly StoreInternalPanneAction $storeInternalPanneAction,
        private readonly RegisterEquipmentReturnAction $registerEquipmentReturnAction,
        private readonly ResolvePanneAction $resolvePanneAction,
        private readonly ReplacePanneEquipmentAction $replacePanneEquipmentAction,
        private readonly CancelAffectationAction $cancelAffectationAction,
        private readonly CreateExternalCollaboratorBonAction $createExternalCollaboratorBonAction,
        private readonly DashboardMetricsService $dashboardMetricsService,
    ) {}

    public function ShowHomePage()
    {
        $metrics = $this->dashboardMetricsService->getAdminMetrics();
        $nbr_equipement = $metrics['nbr_equipement'];
        $nbr_user = $metrics['nbr_user'];
        $nbr_affect = $metrics['nbr_affect'];
        $nbr_panne = $metrics['nbr_panne'];
        $statsParMois = $metrics['statsParMois'];
        $distribution = $metrics['distribution'];
        $growth = $metrics['growth'];

        return view('admin.homedash', ['nbr_equipement' => $nbr_equipement, 'nbr_user' => $nbr_user, 'nbr_affect' => $nbr_affect, 'nbr_panne' => $nbr_panne, 'statsParMois' => $statsParMois, 'distribution' => $distribution, 'growth' => $growth]);
    }

    public function showusers()
    {
        $users = User::where('role', '!=', 'admin')->get();

        return view('admin.listuserpage', ['users' => $users]);
    }

    public function edituserpage(User $user)
    {
        return view('admin.edit_user', ['user' => $user]);
    }

    public function deleteuser(User $user)
    {
        $user_del_message = $user->nom.' '.$user->prenom.' a été supprimée';
        $user->delete();

        return back()->with('deleted', $user_del_message);
    }

    //
    public function ModifyUser(EditRequest $request, User $user)
    {
        $data = $request->only(['nom', 'prenom', 'email', 'role', 'service', 'poste']);

        // Modifier le mot de passe seulement s'il est rempli
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('succès', 'Utilisateur modifié avec succès');
    }

    public function addToolpage()
    {
        $categories = Categorie::toBase()->get();

        return view('admin.addtool', ['categories' => $categories]);
    }

    public function addTool(\App\Http\Requests\StoreToolRequest $request)
    {
        $validated = $request->validated();
        $result = $this->createEquipementAction->handle(
            Auth::user(),
            $validated,
            $request->file('image_path')
        );
        $equipement = $result['equipement'];
        $bon = $result['bon'];
        $pdfPath = $result['pdf_path'];

        $pdf = Pdf::loadView('pdf.bon', [
            'date' => now()->format('d/m/Y'),
            'nom' => Auth::user()->nom ?? 'Admin',
            'prenom' => Auth::user()->prenom ?? '',
            'motif' => 'Ajout de nouvel équipement : '.$equipement->nom,
            'numero_bon' => $bon->id,
            'type' => $bon->statut,
        ]);
        Storage::disk('public')->put($bon->fichier_pdf, $pdf->output());

        return back() // ou ->back()
            ->with('success', 'Équipement ajouté avec succès et un Bon d \'entrée est genéré.')
            ->with('pdf', route('bons.download', ['bon' => $bon->id]));
    }

    public function ShowToolpage()
    {
        $equipements = Equipement::query()
            ->select(['id', 'nom', 'description', 'categorie_id', 'quantite', 'image_path'])
            ->with([
                'categorie:id,nom',
                'affectations:id,equipement_id,user_id,collaborateur_externe_id,quantite_affectee,quantite_retournee,statut',
                'pannes:id,equipement_id,affectation_id,quantite,quantite_retournee_stock,quantite_resolue,statut',
                'pannes.affectation:id,quantite_affectee,quantite_retournee,statut',
            ])
            ->get();

        return view('admin.listtools', ['equipements' => $equipements]);
    }

    public function putToolpage(Equipement $equipement)
    {
        $categories = Categorie::all();

        return view('admin.puttools', ['equipement' => $equipement, 'categories' => $categories]);
    }

    public function putTool(UpdateEquipementRequest $request, Equipement $equipement)
    {
        try {
            Log::info(sprintf("Données reçues pour la mise à jour de l'équipement ID %s : ", $equipement->id).json_encode($request->all()));
            $data = $request->only(['nom', 'etat', 'marque', 'categorie_id', 'description', 'date_acquisition', 'quantite', 'seuil_critique']);
            Log::info(sprintf("Données reçues pour la mise à jour de l'équipement ID %s : ", $equipement->id).json_encode($data));

            if ($request->hasFile('image_path')) {
                $data['image_path'] = $this->storeEquipementImage($request);
            }

            $equipement->update($data);

            return back()->with('success', 'Équipement mis à jour avec succès.');
        } catch (Exception $exception) {
            Log::error("Erreur lors de la mise à jour d'équipement : ".$exception->getMessage());

            return back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour.')
                ->withInput();
        }
    }

    public function DeleteTool(Equipement $equipement)
    {
        $equip_del = $equipement->nom;
        $equipement->delete();

        return back()->with('deleted', "L'equipement ".$equip_del.' a été supprimer avec succès ');
    }

    public function ShowAllAsk()
    {
        $demandes = Demande::with([
            'affectations:id,demande_id,equipement_id,quantite_affectee',
            'equipements' => function ($query): void {
                $query->select(['equipements.id', 'equipements.nom', 'equipements.quantite'])
                    ->with([
                        'affectations:id,equipement_id,user_id,collaborateur_externe_id,quantite_affectee,quantite_retournee,statut',
                        'pannes:id,equipement_id,affectation_id,quantite,quantite_retournee_stock,quantite_resolue,statut',
                        'pannes.affectation:id,quantite_affectee,quantite_retournee,statut',
                    ]);
            },
        ])
            ->where('statut', '=', 'en_attente')
            ->latest()
            ->get();

        return view('admin.asklist', ['demandes' => $demandes]);
    }

    /**
     * Accepte une demande et assigne automatiquement les équipements à l'employé
     */
    public function CheckAsk(ServeDemandeRequest $request, Demande $demande)
    {
        $validated = $request->validated();

        try {
            $result = $this->serveDemandeAction->handle(
                Auth::user(),
                $demande,
                $validated['quantites_a_affecter'] ?? [],
                $validated['dates_retour'] ?? []
            );

            $demande = $result['demande'];
            $message = $result['is_fully_served']
                ? 'La demande a été totalement servie et les équipements ont été affectés.'
                : 'La demande a été partiellement servie. Elle reste en attente pour les quantités restantes.';

            if ($result['pdf_path']) {
                $employe = $demande->user;

                $this->generateBonPdf($result['bon'], [
                    'date' => now()->format('d/m/Y'),
                    'nom' => $employe->nom ?? 'Employé',
                    'prenom' => $employe->prenom ?? '',
                    'motif' => $demande->motif ?? 'Affectation via demande approuvée',
                    'numero_bon' => $result['bon']->id,
                    'type' => $result['bon']->statut,
                    'equipements' => $result['affectations_details'],
                ]);
            }

            event(new DemandeServed($demande->fresh(['user', 'equipements', 'affectations']), $result['affectations_details'] ?? [], $result['bon'] ?? null));

            if ($result['pdf_path']) {
                return back()
                    ->with('success', $message)
                    ->with('pdf', route('bons.download', ['bon' => $result['bon']->id]));
            }

            return back()->with('success', $message);
        } catch (Exception $exception) {
            Log::error("Erreur lors de l'acceptation de demande: ".$exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Rejette une demande d'équipement
     */
    public function CancelAsk(Demande $demande)
    {
        try {
            $demande->update(['statut' => 'rejetee']);

            return back()->with('success', 'La demande a été rejetée avec succès');
        } catch (Exception $exception) {
            Log::error('Erreur lors du rejet de demande: '.$exception->getMessage());

            return back()->with('error', 'Erreur lors du rejet de la demande.');
        }
    }

    public function Showaffectation()
    {
        $equipements_groupes = Categorie::with([
            'equipements' => function ($query): void {
                $query->select(['equipements.id', 'equipements.nom', 'equipements.categorie_id', 'equipements.quantite'])
                    ->with(['pannes:id,equipement_id,affectation_id,quantite,quantite_retournee_stock,quantite_resolue,statut',
                        'pannes.affectation:id,quantite_affectee,quantite_retournee,statut',
                        'affectations:id,equipement_id,user_id,collaborateur_externe_id,quantite_affectee,quantite_retournee,statut'])
                    ->withStock();
            },
        ])->get();

        $employes = User::whereIn('role', ['employe', 'employé', 'employée'])->get();

        return view('admin.affectation', ['equipements_groupes' => $equipements_groupes, 'employes' => $employes]);
    }

    public function HandleAffectation(StoreDirectAffectationRequest $request)
    {
        $validated = $request->validated();

        set_time_limit(120);

        try {
            $result = $this->createDirectAffectationAction->handle(Auth::user(), $validated);

            $employe = $result['employe'];
            $bon = $result['bon'];
            $pdfPath = $result['pdf_path'];

            $this->generateBonPdf($bon, [
                'date' => now()->format('d/m/Y'),
                'nom' => $employe->nom ?? '',
                'prenom' => $employe->prenom ?? '',
                'motif' => $result['motif'],
                'numero_bon' => $bon->id,
                'type' => $bon->statut,
                'equipements' => $result['affectations_details'],
            ]);

            event(new DirectAffectationCreated($employe, $result['motif'], $result['affectations_details'], $bon));

            return back()
                ->with('success', 'Affectation réussie avec succès et un bon de sortie a été généré.')
                ->with('pdf', route('bons.download', ['bon' => $bon->id]));
        } catch (Exception $exception) {
            Log::error("Erreur lors de l'affectation : ".$exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function Showpannes()
    {
        $pannes = Panne::with([
            'user:id,nom,prenom',
            'affectation:id,user_id,quantite_affectee,quantite_retournee,statut',
            'affectation.user:id,nom,prenom',
            'equipement:id,nom,quantite',
            'equipement.affectations:id,equipement_id,user_id,collaborateur_externe_id,quantite_affectee,quantite_retournee,statut',
            'equipement.pannes:id,equipement_id,affectation_id,quantite,quantite_retournee_stock,quantite_resolue,statut',
            'equipement.pannes.affectation:id,quantite_affectee,quantite_retournee,statut',
        ])
            ->where('statut', '=', 'en_attente')
            ->latest()
            ->get();

        $equipementsInternes = Equipement::with([
            'categorie:id,nom',
            'affectations:id,equipement_id,user_id,collaborateur_externe_id,quantite_affectee,quantite_retournee,statut',
            'pannes:id,equipement_id,affectation_id,quantite,quantite_retournee_stock,quantite_resolue,statut',
            'pannes.affectation:id,quantite_affectee,quantite_retournee,statut',
        ])
            ->get()
            ->filter(fn (Equipement $equipement) => $equipement->getQuantiteDisponible() > 0)
            ->values();

        return view('admin.pannelist', ['pannes' => $pannes, 'equipementsInternes' => $equipementsInternes]);
    }

    public function StoreInternalPanne(StoreInternalPanneRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->storeInternalPanneAction->handle(Auth::user(), $validated);

            return back()->with('success', 'Panne interne enregistrée avec succès.');
        } catch (Exception $exception) {
            Log::error('Erreur création panne interne: '.$exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function ShowToollost()
    {
        $equipement_lost = Affectation::with(['equipement', 'user', 'pannes'])
            ->active()
            ->whereNotNull('date_retour')
            ->orderBy('date_retour')
            ->get();

        return view('admin.lost_tools', ['equipement_lost' => $equipement_lost]);
    }

    public function CollaboratorsPage()
    {

        return view('admin.collaborator_external');
    }

    public function HandleCollaborator(\App\Http\Requests\StoreCollaboratorRequest $request)
    {
        $validated = $request->validated();
        $this->createExternalCollaboratorAction->handle(
            $validated,
            $request->file('chemin_carte')
        );

        return back()->with('success', 'Collaborateur ajouté avec succès.');
    }

    public function ShowListCollaborator()
    {
        $collaborateurs = CollaborateurExterne::get();

        return view('admin.list_collaborator', ['collaborateurs' => $collaborateurs]);
    }

    public function destroy(CollaborateurExterne $CollaborateurExterne)
    {
        $CollaborateurExterne->delete();

        return back()->with('remove', 'le collaborateur a été supprimée');
    }

    public function ShowBons()
    {
        $bons = Bon::latest()->get();

        return view('admin.list_bons', ['bons' => $bons]);
    }

    public function downloadBon(Bon $bon)
    {
        abort_if(! $bon->fichier_pdf || ! Storage::disk('public')->exists($bon->fichier_pdf), 404);

        return response()->download(
            Storage::disk('public')->path($bon->fichier_pdf),
            basename($bon->fichier_pdf)
        );
    }

    public function CreateBon()
    {
        $collaborateurs = CollaborateurExterne::orderBy('nom')->orderBy('prenom')->get();
        $equipements_groupes = Categorie::with([
            'equipements' => function ($query): void {
                $query->with(['affectations', 'pannes.affectation']);
            },
        ])->get();

        return view('admin.bon_external_collaborator', ['collaborateurs' => $collaborateurs, 'equipements_groupes' => $equipements_groupes]);
    }

    public function HandleBon(\App\Http\Requests\StoreBonRequest $request)
    {
        $validated = $request->validated();
        $result = $this->createExternalCollaboratorBonAction->handle(Auth::user(), $validated);
        $bon = $result['bon'];

        $pdf = Pdf::loadView('pdf.bon', [
            'date' => now()->format('d/m/Y'),
            'nom' => $result['collaborateur']->nom ?? 'Admin',
            'prenom' => $result['collaborateur']->prenom ?? '',
            'motif' => $validated['motif'],
            'numero_bon' => $bon->id,
            'type' => $bon->statut,
            'equipements' => $result['equipements_info'],
        ]);
        $pdf->setPaper('A5', 'portrait');
        Storage::disk('public')->put($result['pdf_path'], $pdf->output());

        $message = $validated['type'] === 'entrée'
            ? 'Bon d’entrée généré avec succès pour le collaborateur externe.'
            : 'Bon de sortie généré avec succès pour le collaborateur externe.';

        return back()
            ->with('success', $message)
            ->with('pdf', route('bons.download', ['bon' => $bon->id]));
    }

    public function BackTool(RegisterEquipmentReturnRequest $request, Affectation $affectation)
    {
        $validated = $request->validated();

        try {
            $result = $this->registerEquipmentReturnAction->handle($affectation, $validated);
            $affectation = $result['affectation'];
            $bon = $result['bon'];
            $pdfPath = $result['pdf_path'];

            $this->generateBonPdf($bon, [
                'date' => now()->format('d/m/Y'),
                'nom' => $affectation->getNomDestinataire(),
                'prenom' => '',
                'motif' => $bon->motif,
                'numero_bon' => $bon->id,
                'type' => $bon->statut,
                'equipements' => [[
                    'nom' => $affectation->equipement->nom,
                    'quantite' => $result['total_returned'],
                ]],
            ]);

            event(new EquipmentReturned($affectation, $result['healthy_returned'], $result['broken_returned'], $bon));

            return back()
                ->with('success', 'Retour du matériel enregistré avec succès')
                ->with('pdf', route('bons.download', ['bon' => $bon->id]));
        } catch (Exception $exception) {
            Log::error("Erreur lors du retour d'équipement: ".$exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function Showlistaffectation()
    {
        $affectations = Affectation::with([
            'equipement:id,nom',
            'user:id,nom,prenom,email',
            'collaborateurExterne:id,nom,prenom',
            'demande:id',
            'pannes:id,affectation_id,equipement_id,quantite,quantite_retournee_stock,quantite_resolue,statut',
        ])
            ->withCount('pannes')
            ->latest()
            ->get();

        return view('admin.affectlist', ['affectations' => $affectations]);
    }

    public function CancelAffectation(string $affectationId)
    {
        try {
            $result = $this->cancelAffectationAction->handle((int) $affectationId);

            return back()->with('success', sprintf(
                'L’affectation de « %s » a été annulée avec succès.',
                $result['equipement_nom']
            ));
        } catch (Exception $exception) {
            Log::error("Erreur lors de l'annulation d'affectation: ".$exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function LoadingAsk(Demande $demande)
    {
        $demande->update(['statut' => 'en_attente']);

        return back()->with('hold', 'Demande mise en attente');
    }

    public function ShowRapport()
    {
        $rapports = Rapport::orderBy('created_at', 'desc')->get();

        return view('admin.list_rapport', ['rapports' => $rapports]);
    }

    /**
     * Résout une panne en la marquant comme résolue
     * Implique que l'équipement est réparé ou remplacé
     */
    public function PutPanne(ResolvePanneRequest $request, Panne $panne)
    {
        $validated = $request->validated();

        try {
            $result = $this->resolvePanneAction->handle($panne, (int) $validated['quantite_resolue']);
            $panne = $result['panne'];

            Log::info(sprintf('Panne %s résolue par admin', $panne->id), [
                'equipement_id' => $panne->equipement_id,
                'quantite_resolue' => $result['resolved_quantity'],
            ]);

            event(new PanneResolved($panne, $result['resolved_quantity']));

            return back()->with('success', sprintf(
                '%d équipement(s) marqué(s) comme réparé(s).',
                $result['resolved_quantity']
            ));
        } catch (Exception $exception) {
            Log::error('Erreur résolution panne admin: '.$exception->getMessage());

            return back()->with('error', 'Erreur lors de la résolution de la panne. Veuillez réessayer.');
        }
    }

    public function ReplacePanne(ReplacePanneEquipmentRequest $request, Panne $panne)
    {
        $validated = $request->validated();

        try {
            $result = $this->replacePanneEquipmentAction->handle(
                Auth::user(),
                $panne,
                (int) $validated['quantite_remplacement']
            );

            $panne = $result['panne'];
            $bon = $result['bon'];
            $affectationRemplacement = $result['replacement_affectation'];
            $destinataire = $affectationRemplacement->user;

            $this->generateBonPdf($bon, [
                'date' => now()->format('d/m/Y'),
                'nom' => $destinataire->nom ?? '',
                'prenom' => $destinataire->prenom ?? '',
                'motif' => 'Remplacement d’équipement en panne : '.$panne->equipement->nom,
                'numero_bon' => $bon->id,
                'type' => $bon->statut,
                'equipements' => [[
                    'nom' => $panne->equipement->nom,
                    'quantite' => $affectationRemplacement->quantite_affectee,
                    'date_retour' => $affectationRemplacement->date_retour
                        ? $affectationRemplacement->date_retour->format('Y-m-d')
                        : null,
                ]],
            ]);

            event(new PanneReplacementCompleted($panne, $result['replacement_quantity'], $bon));

            return back()
                ->with('success', 'Le remplacement a été enregistré avec succès.')
                ->with('pdf', route('bons.download', ['bon' => $bon->id]));
        } catch (Exception $exception) {
            Log::error('Erreur remplacement panne admin: '.$exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    // ============================================================================
    // MÉTHODES PRIVÉES - Utilitaires de refactorisation
    // ============================================================================

    /**
     * Stocke l'image de l'équipement localement
     */
    private function storeEquipementImage(\Illuminate\Http\Request $request): string
    {
        $file = $request->file('image_path');
        $nomNettoye = preg_replace('/[^a-zA-Z0-9-_]/', '', mb_strtolower(str_replace(' ', '-', $request->nom)));
        $imageName = time().'_'.$nomNettoye.'.'.$file->getClientOriginalExtension();

        $file->move(public_path('pictures/equipements'), $imageName);

        return 'pictures/equipements/'.$imageName;
    }

    /**
     * Génère le PDF d'un bon
     */
    private function generateBonPdf(Bon $bon, array $data): void
    {
        $pdf = Pdf::loadView('pdf.bon', $data);
        Storage::disk('public')->put($bon->fichier_pdf, $pdf->output());
    }
}
