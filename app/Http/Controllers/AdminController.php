<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EditRequest;
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
use App\Services\WorkflowNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function ShowHomePage()
    {
        $nbr_equipement = (int) Equipement::sum('quantite');
        $nbr_user = User::count();
        $nbr_affect = (int) Affectation::with('pannes')
            ->get()
            ->sum(fn (Affectation $affectation) => $affectation->getQuantiteActive());
        $nbr_panne = (int) Panne::with(['equipement', 'affectation'])
            ->where('statut', '!=', 'resolu')
            ->get()
            ->sum(fn (Panne $panne) => $panne->getQuantiteNonResolue());

        $now = Carbon::now();
        $user_this_month = User::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        $user_before_month = User::where('created_at', '<', $now->copy()->startOfMonth())->count();

        $growth = 0;
        if ($nbr_user > 0) {
            $growth = (($user_this_month - $user_before_month) / $nbr_user) * 100;
        }

        $statsParMois = [];
        for ($i = 1; $i <= 12; $i++) {
            $debut = Carbon::create(null, $i, 1)->startOfMonth();
            $fin = Carbon::create(null, $i, 1)->endOfMonth();

            $statsParMois[$i] = (int) Affectation::whereBetween('created_at', [$debut, $fin])
                ->sum('quantite_affectee');
        }

        $distribution = Categorie::with('equipements')->get()->map(function (Categorie $categorie): array {
            return [
                'label' => $categorie->nom,
                'count' => (int) $categorie->equipements->sum('quantite'),
            ];
        });

        return view('admin.homedash', compact(
            'nbr_equipement',
            'nbr_user',
            'nbr_affect',
            'nbr_panne',
            'statsParMois',
            'distribution',
            'growth'
        ));
    }

    public function showusers()
    {
        $users = User::where('role', '!=', 'admin')->get();

        return view('admin.listuserpage', compact('users'));
    }

    public function edituserpage(User $user)
    {
        return view('admin.edit_user', compact('user'));
    }

    public function deleteuser(User $user)
    {
        $user_del_message = $user->nom.' '.$user->prenom.' a été supprimée';
        $user->delete();

        return redirect()->back()->with('deleted', $user_del_message);
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

        return redirect()->back()->with('succès', 'Utilisateur modifié avec succès');
    }

    public function addToolpage()
    {
        $categories = Categorie::toBase()->get();

        return view('admin.addtool', compact('categories'));
    }

    public function addTool(\App\Http\Requests\StoreToolRequest $request)
    {
        $validated = $request->validated();
        $imagePath = null;

        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $nomNettoye = preg_replace('/[^a-zA-Z0-9-_]/', '', mb_strtolower(str_replace(' ', '-', $validated['nom'])));
            $imageName = time().'_'.$nomNettoye.'.'.$file->getClientOriginalExtension();
            $file->move(public_path('pictures/equipements'), $imageName);
            $imagePath = 'pictures/equipements/'.$imageName;
        }

        $equipement = Equipement::create([
            'nom' => $validated['nom'],
            'marque' => $validated['marque'],
            'description' => $validated['description'],
            'date_acquisition' => $validated['date_acquisition'],
            'quantite' => $validated['quantite'],
            'seuil_critique' => $validated['seuil_critique'],
            'image_path' => $imagePath,
            'categorie_id' => $validated['categorie_id'],
        ]);

        $user = Auth::user();
        $bon = new Bon();
        $bon->motif = 'Ajout de nouvel équipement : '.$equipement->nom;
        $bon->user_id = $user->id;
        $bon->statut = 'entrée';
        $pdfName = 'bon_entree_'.$equipement->id.'.pdf';
        $pdfPath = 'bon_entree/'.$pdfName;
        $bon->fichier_pdf = $pdfPath;
        $bon->save();
        $pdf = Pdf::loadView('pdf.bon', [
            'date' => now()->format('d/m/Y'),
            'nom' => $user->nom ?? 'Admin',
            'prenom' => $user->prenom ?? '',
            'motif' => 'Ajout de nouvel équipement : '.$equipement->nom,
            'numero_bon' => $bon->id,
            'type' => $bon->statut,
        ]);
        Storage::disk('public')->put($pdfPath, $pdf->output());

        return redirect()->back() // ou ->back()
            ->with('success', 'Équipement ajouté avec succès et un Bon d \'entrée est genéré.')
            ->with('pdf', asset('storage/'.$pdfPath));
    }

    public function ShowToolpage()
    {
        $equipements = Equipement::with([
            'categorie',
            'affectations',
            'pannes.affectation', // sous-relation affectation dans chaque panne
        ])->get();

        return view('admin.listtools', compact('equipements'));
    }

    public function putToolpage(Equipement $equipement)
    {
        $categories = Categorie::all();

        return view('admin.puttools', compact('equipement', 'categories'));
    }

    public function putTool(UpdateEquipementRequest $request, Equipement $equipement)
    {
        try {
            Log::info("Données reçues pour la mise à jour de l'équipement ID {$equipement->id} : ".json_encode($request->all()));
            $data = $request->only(['nom', 'etat', 'marque', 'categorie_id', 'description', 'date_acquisition', 'quantite', 'seuil_critique']);
            Log::info("Données reçues pour la mise à jour de l'équipement ID {$equipement->id} : ".json_encode($data));

            if ($request->hasFile('image_path')) {
                $data['image_path'] = $this->storeEquipementImage($request);
            }

            $equipement->update($data);

            return redirect()->back()->with('success', 'Équipement mis à jour avec succès.');
        } catch (Exception $e) {
            Log::error("Erreur lors de la mise à jour d'équipement : ".$e->getMessage());

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour.')
                ->withInput();
        }
    }

    public function DeleteTool(Equipement $equipement)
    {
        $equip_del = $equipement->nom;
        $equipement->delete();

        return redirect()->back()->with('deleted', "L'equipement ".$equip_del.' a été supprimer avec succès ');
    }

    public function ShowAllAsk()
    {
        $demandes = Demande::with(['equipements', 'affectations'])
            ->where('statut', '=', 'en_attente')
            ->latest()
            ->get();

        return view('admin.asklist', compact('demandes'));
    }

    /**
     * Accepte une demande et assigne automatiquement les équipements à l'employé
     */
    public function CheckAsk(Request $request, Demande $demande)
    {
        $validated = $request->validate([
            'quantites_a_affecter' => 'required|array',
            'quantites_a_affecter.*' => 'nullable|integer|min:0',
            'dates_retour' => 'nullable|array',
            'dates_retour.*' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            // Assigner automatiquement les équipements
            $result = $this->assignEquipmentsFromDemande(
                $demande,
                $validated['quantites_a_affecter'] ?? [],
                $validated['dates_retour'] ?? []
            );

            $demande->load(['equipements', 'affectations']);

            if ($demande->estEntierementServie()) {
                $demande->update(['statut' => 'acceptee']);
                $message = 'La demande a été totalement servie et les équipements ont été affectés.';
            } else {
                $message = 'La demande a été partiellement servie. Elle reste en attente pour les quantités restantes.';
            }

            DB::commit();

            $this->workflowNotificationService->notifyDemandeServed(
                $demande->fresh(['user', 'equipements', 'affectations']),
                $result['affectations_details'] ?? [],
                $result['bon'] ?? null
            );

            if ($result['pdf_path']) {
                return redirect()->back()
                    ->with('success', $message)
                    ->with('pdf', asset('storage/'.$result['pdf_path']));
            }

            return redirect()->back()->with('success', $message);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'acceptation de demande: ".$e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Rejette une demande d'équipement
     */
    public function CancelAsk(Demande $demande)
    {
        try {
            $demande->update(['statut' => 'rejetee']);

            return redirect()->back()->with('success', 'La demande a été rejetée avec succès');
        } catch (Exception $e) {
            Log::error('Erreur lors du rejet de demande: '.$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors du rejet de la demande.');
        }
    }

    public function Showaffectation()
    {
        $equipements_groupes = Categorie::with([
            'equipements' => function ($query) {
                $query->withStock()
                    ->with(['pannes', 'affectations']);
            },
        ])->get();

        $employes = User::whereIn('role', ['employe', 'employé', 'employée'])->get();

        return view('admin.affectation', compact('equipements_groupes', 'employes'));
    }

    public function HandleAffectation(Request $request)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:users,id',
            'motif' => 'required|string|max:500',
            'equipements' => 'required|array|min:1',
            'equipements.*' => 'required|exists:equipements,id',
            'quantites' => 'required|array|min:1',
            'quantites.*' => 'required|integer|min:1',
            'dates_retour' => 'nullable|array',
            'dates_retour.*' => 'nullable|date',
        ], [
            'equipements.required' => 'le champ equipement est requis',
            'quantites.required' => 'le champ quantité est requis',
        ]);

        set_time_limit(120);
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $employe = User::findOrFail($validated['employe_id']);

            if (! in_array($employe->role, ['employe', 'employé', 'employée'], true)) {
                throw new Exception("L'utilisateur sélectionné n'est pas un employé.");
            }

            $lignesAffectation = $this->normalizeDirectAffectationLines(
                $validated['equipements'],
                $validated['quantites'],
                $validated['dates_retour'] ?? []
            );

            // Charger les équipements en bulk
            $equipementIds = array_values(array_unique(array_column($lignesAffectation, 'equipement_id')));
            $equipements = Equipement::whereIn('id', $equipementIds)->get()->keyBy('id');
            $affectationsDetails = [];
            $quantitesReservees = [];

            foreach ($lignesAffectation as $ligneAffectation) {
                $equipement_id = $ligneAffectation['equipement_id'];
                $quantite = $ligneAffectation['quantite'];
                $rawDate = $ligneAffectation['date_retour'];

                $equipement = $equipements->get($equipement_id);

                if (! $equipement) {
                    throw new Exception("Équipement ID $equipement_id introuvable.");
                }

                // Valider la disponibilité
                $this->validateAffectationAvailability(
                    $equipement,
                    $quantite,
                    $quantitesReservees[$equipement_id] ?? 0
                );

                Affectation::create([
                    'equipement_id' => $equipement_id,
                    'user_id' => $employe->id,
                    'demande_id' => null,
                    'date_retour' => $rawDate ?: null,
                    'created_by' => $user->nom.' '.$user->prenom,
                    'quantite_affectee' => $quantite,
                    'statut' => 'active',
                ]);

                $quantitesReservees[$equipement_id] = ($quantitesReservees[$equipement_id] ?? 0) + $quantite;

                $affectationsDetails[] = [
                    'nom' => $equipement->nom,
                    'reference' => $equipement->reference ?? '',
                    'quantite' => $quantite,
                    'date_retour' => $rawDate ?: null,
                ];
            }

            $pdfName = 'bon_sortie_'.$employe->id.'_'.now()->timestamp.'.pdf';
            $pdfPath = 'bon_sortie/'.$pdfName;

            $bon = Bon::create([
                'user_id' => $employe->id,
                'motif' => $validated['motif'],
                'statut' => 'sortie',
                'fichier_pdf' => $pdfPath,
            ]);

            DB::commit();

            $this->generateBonPdf($bon, [
                'date' => now()->format('d/m/Y'),
                'nom' => $employe->nom ?? '',
                'prenom' => $employe->prenom ?? '',
                'motif' => $validated['motif'],
                'numero_bon' => $bon->id,
                'type' => $bon->statut,
                'equipements' => $affectationsDetails,
            ]);

            $this->workflowNotificationService->notifyDirectAffectation(
                $employe,
                $validated['motif'],
                $affectationsDetails,
                $bon
            );

            return redirect()->back()
                ->with('success', 'Affectation réussie avec succès et un bon de sortie a été généré.')
                ->with('pdf', asset('/storage/'.$pdfPath));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'affectation : ".$e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function Showpannes()
    {
        $pannes = Panne::with(['equipement', 'user', 'affectation.user'])
            ->where('statut', '=', 'en_attente')
            ->latest()
            ->get();

        $equipementsInternes = Equipement::with('categorie')
            ->get()
            ->filter(fn (Equipement $equipement) => $equipement->getQuantiteDisponible() > 0)
            ->values();

        return view('admin.pannelist', compact('pannes', 'equipementsInternes'));
    }

    public function StoreInternalPanne(Request $request)
    {
        $validated = $request->validate([
            'equipement_id' => 'required|integer|exists:equipements,id',
            'quantite' => 'required|integer|min:1',
            'description' => 'required|string|min:10|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $equipement = Equipement::findOrFail($validated['equipement_id']);

            if ($validated['quantite'] > $equipement->getQuantiteDisponible()) {
                throw new Exception(sprintf(
                    'Vous ne pouvez déclarer en panne interne que %d unité(s) pour « %s ».',
                    $equipement->getQuantiteDisponible(),
                    $equipement->nom
                ));
            }

            Panne::create([
                'equipement_id' => $equipement->id,
                'affectation_id' => null,
                'user_id' => Auth::id(),
                'quantite' => (int) $validated['quantite'],
                'quantite_retournee_stock' => 0,
                'quantite_resolue' => 0,
                'description' => $validated['description'],
                'statut' => 'en_attente',
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Panne interne enregistrée avec succès.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur création panne interne: '.$e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ShowToollost()
    {
        $equipement_lost = Affectation::with(['equipement', 'user', 'pannes'])
            ->whereDate('date_retour', '<=', now())    // date de retour dépassée
            ->active()                                 // statut non retourné
            ->whereNotNull('date_retour')              // on s'assure que la date de retour est bien définie
            ->get();

        return view('admin.lost_tools', compact('equipement_lost'));
    }

    public function CollaboratorsPage()
    {

        return view('admin.collaborator_external');
    }

    public function HandleCollaborator(\App\Http\Requests\StoreCollaboratorRequest $request)
    {
        $validated = $request->validated();
        $cartePath = null;
        if ($request->hasFile('chemin_carte')) {
            $file = $request->file('chemin_carte');
            $filename = 'carte_'.time().'_'.preg_replace('/\s+/', '_', $validated['nom']).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('collaborateurs/cartes'), $filename);
            $cartePath = 'collaborateurs/cartes/'.$filename;
        }
        CollaborateurExterne::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'carte_chemin' => $cartePath,
        ]);

        return redirect()->back()->with('success', 'Collaborateur ajouté avec succès.');
    }

    public function ShowListCollaborator()
    {
        $collaborateurs = CollaborateurExterne::get();

        return view('admin.list_collaborator', compact('collaborateurs'));
    }

    public function destroy(CollaborateurExterne $CollaborateurExterne)
    {
        $CollaborateurExterne->delete();

        return redirect()->back()->with('remove', 'le collaborateur a été supprimée');
    }

    public function ShowBons()
    {
        $bons = Bon::latest()->get();

        return view('admin.list_bons', compact('bons'));
    }

    public function CreateBon()
    {
        $collaborateurs = CollaborateurExterne::orderBy('nom')->orderBy('prenom')->get();
        $equipements_groupes = Categorie::with([
            'equipements' => function ($query) {
                $query->with(['affectations', 'pannes.affectation']);
            },
        ])->get();

        return view('admin.bon_external_collaborator', compact('collaborateurs', 'equipements_groupes'));
    }

    public function HandleBon(\App\Http\Requests\StoreBonRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $collaborateur = CollaborateurExterne::findOrFail($validated['collaborateur_id']);
        $pdfPath = 'bon_collaborateurs/bon_collab_'.time().'.pdf';
        $bon = Bon::create([
            'collaborateur_externe_id' => $collaborateur->id,
            'motif' => $validated['motif'],
            'statut' => $validated['type'],
            'fichier_pdf' => $pdfPath,
        ]);

        // Attach equipements to bon for legacy compatibility
        $bonEquipements = collect($validated['equipements'])->mapWithKeys(fn ($equipementId, $index) => [
            $equipementId => ['quantite' => (int) ($validated['quantites'][$index] ?? 0)],
        ])->filter(fn ($item) => $item['quantite'] > 0)->all();

        if (! empty($bonEquipements)) {
            $bon->equipements()->attach($bonEquipements);
        }

        // Create affectations for outgoing bons (new centralized approach)
        if ($validated['type'] === 'sortie') {
            foreach ($validated['equipements'] as $index => $equipementId) {
                $quantite = (int) ($validated['quantites'][$index] ?? 0);
                if ($quantite > 0) {
                    Affectation::create([
                        'equipement_id' => (int) $equipementId,
                        'collaborateur_externe_id' => $collaborateur->id,
                        'quantite_affectee' => $quantite,
                        'statut' => 'active',
                        'created_by' => $user->nom.' '.$user->prenom,
                    ]);
                }
            }
        }

        $equipementsInfo = collect($validated['equipements'])->map(fn ($equipementId, $index) => [
            'nom' => Equipement::find($equipementId)?->nom ?? 'Inconnu',
            'quantite' => (int) ($validated['quantites'][$index] ?? 0),
        ])->filter(fn ($item) => $item['quantite'] > 0)->values()->all();

        $pdf = Pdf::loadView('pdf.bon', [
            'date' => now()->format('d/m/Y'),
            'nom' => $collaborateur->nom ?? 'Admin',
            'prenom' => $collaborateur->prenom ?? '',
            'motif' => $validated['motif'],
            'numero_bon' => $bon->id,
            'type' => $bon->statut,
            'equipements' => $equipementsInfo,
        ]);
        $pdf->setPaper('A5', 'portrait');
        Storage::disk('public')->put($pdfPath, $pdf->output());

        return redirect()->back()
            ->with('success', 'Bon généré avec succès pour le collaborateur externe.')
            ->with('pdf', asset('storage/'.$pdfPath));
    }

    public function BackTool(Request $request, Affectation $affectation)
    {
        $validated = $request->validate([
            'quantite_saine_retournee' => 'nullable|integer|min:0',
            'pannes_retournees' => 'nullable|array',
            'pannes_retournees.*' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $affectation->load(['equipement', 'user', 'collaborateurExterne', 'pannes' => function ($query) {
                $query->where('statut', '!=', 'resolu');
            }]);

            $quantiteSaineRetournee = (int) ($validated['quantite_saine_retournee'] ?? 0);
            $pannesRetournees = $validated['pannes_retournees'] ?? [];
            $quantitePanneRetournee = 0;

            if ($quantiteSaineRetournee > $affectation->getQuantiteSaineActive()) {
                throw new Exception(sprintf(
                    'Vous ne pouvez retourner que %d unité(s) saine(s) pour cette affectation.',
                    $affectation->getQuantiteSaineActive()
                ));
            }

            foreach ($affectation->pannes as $panne) {
                $quantiteRetourPanne = (int) ($pannesRetournees[$panne->id] ?? 0);

                if ($quantiteRetourPanne > $panne->getQuantiteEncoreChezEmploye()) {
                    throw new Exception(sprintf(
                        'La quantité retournée pour la panne #%d dépasse le maximum autorisé (%d).',
                        $panne->id,
                        $panne->getQuantiteEncoreChezEmploye()
                    ));
                }

                if ($quantiteRetourPanne > 0) {
                    $panne->update([
                        'quantite_retournee_stock' => $panne->getQuantiteRetourneeAuStock() + $quantiteRetourPanne,
                    ]);
                }

                $quantitePanneRetournee += $quantiteRetourPanne;
            }

            $quantiteRetourneeTotale = $quantiteSaineRetournee + $quantitePanneRetournee;

            if ($quantiteRetourneeTotale <= 0) {
                throw new Exception('Veuillez saisir au moins une quantité à retourner.');
            }

            if ($quantiteRetourneeTotale > $affectation->getQuantiteActive()) {
                throw new Exception(sprintf(
                    'Vous ne pouvez retourner que %d unité(s) au total pour cette affectation.',
                    $affectation->getQuantiteActive()
                ));
            }

            $nouvelleQuantiteRetournee = $affectation->getQuantiteRetournee() + $quantiteRetourneeTotale;

            $updateData = [
                'quantite_retournee' => $nouvelleQuantiteRetournee,
                'statut' => $nouvelleQuantiteRetournee >= $affectation->quantite_affectee ? 'retourné' : 'retour_partiel',
            ];

            // Record actual return timestamp for audit trail
            if ($nouvelleQuantiteRetournee >= $affectation->quantite_affectee) {
                $updateData['returned_at'] = now();
            }

            $affectation->update($updateData);

            $equipement = $affectation->equipement;
            $destinataireName = $affectation->getNomDestinataire();

            $pdfName = 'bon_entree_retour_'.$affectation->id.'_'.now()->timestamp.'.pdf';
            $pdfPath = 'bon_entree/'.$pdfName;

            // Create return entry bon with appropriate recipient type
            $bonData = [
                'motif' => sprintf(
                    'Retour de matériel : %s (total: %d, sain: %d, en panne: %d)',
                    $equipement->nom,
                    $quantiteRetourneeTotale,
                    $quantiteSaineRetournee,
                    $quantitePanneRetournee
                ),
                'statut' => 'entrée',
                'fichier_pdf' => $pdfPath,
            ];

            if ($affectation->estPourCollaborateur()) {
                $bonData['collaborateur_externe_id'] = $affectation->collaborateur_externe_id;
            } else {
                $bonData['user_id'] = $affectation->user_id;
            }

            $bon = Bon::create($bonData);

            $this->generateBonPdf($bon, [
                'date' => now()->format('d/m/Y'),
                'nom' => $destinataireName,
                'prenom' => '', // Name is already complete
                'motif' => $bon->motif,
                'numero_bon' => $bon->id,
                'type' => $bon->statut,
                'equipements' => [[
                    'nom' => $equipement->nom,
                    'quantite' => $quantiteRetourneeTotale,
                ]],
            ]);

            DB::commit();

            $this->workflowNotificationService->notifyEquipmentReturned(
                $affectation->fresh(['user', 'collaborateurExterne', 'equipement', 'pannes']),
                $quantiteSaineRetournee,
                $quantitePanneRetournee,
                $bon
            );

            return redirect()->back()
                ->with('success', 'Retour du matériel enregistré avec succès. Un bon d’entrée a été généré.')
                ->with('pdf', asset('storage/'.$pdfPath));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors du retour d'équipement: ".$e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function Showlistaffectation()
    {
        $affectations = Affectation::with(['equipement', 'user', 'collaborateurExterne', 'demande', 'pannes'])
            ->withCount('pannes')
            ->latest()
            ->get();

        return view('admin.affectlist', compact('affectations'));
    }

    public function CancelAffectation(string $affectationId)
    {
        try {
            DB::beginTransaction();

            $affectation = Affectation::with(['equipement', 'user', 'demande'])
                ->findOrFail((int) $affectationId);

            $affectation->setAttribute('pannes_count', $affectation->pannes()->count());

            if (! $affectation->peutEtreAnnulee()) {
                throw new Exception($affectation->getMotifBlocageAnnulation() ?? 'Cette affectation ne peut pas être annulée.');
            }

            $demande = $affectation->demande;
            $equipementNom = $affectation->equipement->nom ?? 'Équipement';

            Affectation::whereKey($affectation->id)->delete();

            if ($demande) {
                $demande->refresh()->load(['equipements', 'affectations']);
                $demande->update([
                    'statut' => $demande->estEntierementServie() ? 'acceptee' : 'en_attente',
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', sprintf(
                'L’affectation de « %s » a été annulée avec succès.',
                $equipementNom
            ));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'annulation d'affectation: ".$e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function LoadingAsk(Demande $demande)
    {
        $demande->update(['statut' => 'en_attente']);

        return redirect()->back()->with('hold', 'Demande mise en attente');
    }

    public function ShowRapport()
    {
        $rapports = Rapport::orderBy('created_at', 'desc')->get();

        return view('admin.list_rapport', compact('rapports'));
    }

    /**
     * Résout une panne en la marquant comme résolue
     * Implique que l'équipement est réparé ou remplacé
     */
    public function PutPanne(Request $request, Panne $panne)
    {
        $validated = $request->validate([
            'quantite_resolue' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $panne->load(['equipement', 'affectation']);

            $quantiteResolvable = $panne->getQuantiteResolvable();

            if ($quantiteResolvable <= 0) {
                throw new Exception('Aucune quantité n’est encore disponible pour résolution sur cette panne.');
            }

            if ($validated['quantite_resolue'] > $quantiteResolvable) {
                throw new Exception(sprintf(
                    'Vous ne pouvez résoudre que %d unité(s) pour cette panne.',
                    $quantiteResolvable
                ));
            }

            $panne->quantite_resolue = $panne->getQuantiteResolue() + (int) $validated['quantite_resolue'];
            $panne->statut = $panne->getQuantiteNonResolue() === 0 ? 'resolu' : 'en_attente';
            $panne->save();

            // Log de la résolution
            Log::info("Panne {$panne->id} résolue par admin", [
                'equipement_id' => $panne->equipement_id,
                'quantite_resolue' => $validated['quantite_resolue'],
            ]);

            DB::commit();

            $this->workflowNotificationService->notifyPanneResolved(
                $panne->fresh(['equipement', 'affectation.user', 'user']),
                (int) $validated['quantite_resolue']
            );

            return redirect()->back()->with('success', sprintf(
                '%d équipement(s) marqué(s) comme réparé(s).',
                $validated['quantite_resolue']
            ));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur résolution panne admin: '.$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors de la résolution de la panne. Veuillez réessayer.');
        }
    }

    public function ReplacePanne(Request $request, Panne $panne)
    {
        $validated = $request->validate([
            'quantite_remplacement' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $panne->load(['equipement', 'affectation.user']);

            if ($panne->estInterne() || ! $panne->affectation) {
                throw new Exception('Le remplacement ne peut se faire que sur une panne liée à une affectation active.');
            }

            $quantiteRemplacable = $panne->getQuantiteRemplacable();

            if ($quantiteRemplacable <= 0) {
                throw new Exception('Aucune quantité n’est disponible pour un remplacement immédiat.');
            }

            if ($validated['quantite_remplacement'] > $quantiteRemplacable) {
                throw new Exception(sprintf(
                    'Vous ne pouvez remplacer que %d unité(s) pour cette panne.',
                    $quantiteRemplacable
                ));
            }

            $quantiteRemplacement = (int) $validated['quantite_remplacement'];
            $affectationOrigine = $panne->affectation;
            $utilisateur = $affectationOrigine->user;
            $user = Auth::user();

            $panne->quantite_retournee_stock = $panne->getQuantiteRetourneeAuStock() + $quantiteRemplacement;
            $panne->statut = $panne->getQuantiteNonResolue() === 0 ? 'resolu' : 'en_attente';
            $panne->save();

            $nouvelleQuantiteRetournee = $affectationOrigine->getQuantiteRetournee() + $quantiteRemplacement;
            $affectationOrigine->update([
                'quantite_retournee' => $nouvelleQuantiteRetournee,
                'statut' => $nouvelleQuantiteRetournee >= $affectationOrigine->quantite_affectee ? 'retourné' : 'retour_partiel',
            ]);

            $affectationRemplacement = Affectation::create([
                'equipement_id' => $panne->equipement_id,
                'user_id' => $utilisateur->id,
                'demande_id' => null,
                'date_retour' => $affectationOrigine->date_retour,
                'created_by' => $user->nom.' '.$user->prenom,
                'quantite_affectee' => $quantiteRemplacement,
                'quantite_retournee' => 0,
                'statut' => 'active',
            ]);

            $pdfName = 'bon_sortie_remplacement_'.$panne->id.'_'.now()->timestamp.'.pdf';
            $pdfPath = 'bon_sortie/'.$pdfName;

            $bon = Bon::create([
                'user_id' => $utilisateur->id,
                'motif' => 'Remplacement d’équipement en panne : '.$panne->equipement->nom,
                'statut' => 'sortie',
                'fichier_pdf' => $pdfPath,
            ]);

            $this->generateBonPdf($bon, [
                'date' => now()->format('d/m/Y'),
                'nom' => $utilisateur->nom ?? '',
                'prenom' => $utilisateur->prenom ?? '',
                'motif' => 'Remplacement d’équipement en panne : '.$panne->equipement->nom,
                'numero_bon' => $bon->id,
                'type' => $bon->statut,
                'equipements' => [[
                    'nom' => $panne->equipement->nom,
                    'reference' => $panne->equipement->reference ?? '',
                    'quantite' => $affectationRemplacement->quantite_affectee,
                    'date_retour' => $affectationRemplacement->date_retour
                        ? $affectationRemplacement->date_retour->format('Y-m-d')
                        : null,
                ]],
            ]);

            DB::commit();

            $this->workflowNotificationService->notifyPanneReplacement(
                $panne->fresh(['equipement', 'affectation.user', 'user']),
                $quantiteRemplacement,
                $bon
            );

            return redirect()->back()
                ->with('success', 'Le remplacement a été enregistré avec succès.')
                ->with('pdf', asset('storage/'.$pdfPath));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur remplacement panne admin: '.$e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ============================================================================
    // MÉTHODES PRIVÉES - Utilitaires de refactorisation
    // ============================================================================

    /**
     * Stocke l'image de l'équipement localement
     */
    private function storeEquipementImage(Request $request): string
    {
        $file = $request->file('image_path');
        $nomNettoye = preg_replace('/[^a-zA-Z0-9-_]/', '', mb_strtolower(str_replace(' ', '-', $request->nom)));
        $imageName = time().'_'.$nomNettoye.'.'.$file->getClientOriginalExtension();

        $file->move(public_path('pictures/equipements'), $imageName);

        return 'pictures/equipements/'.$imageName;
    }

    /**
     * Crée un bon d'entrée pour un équipement
     */
    private function createBonEntree(Equipement $equipement): Bon
    {
        $user = Auth::user();
        $bon = new Bon();
        $bon->motif = 'Ajout de nouvel équipement : '.$equipement->nom;
        $bon->user_id = $user->id;
        $bon->statut = 'entrée';
        $pdfName = 'bon_entree_'.$equipement->id.'.pdf';
        $pdfPath = 'bon_entree/'.$pdfName;
        $bon->fichier_pdf = $pdfPath;
        $bon->save();

        $this->generateBonPdf($bon, [
            'date' => now()->format('d/m/Y'),
            'nom' => $user->nom ?? 'Admin',
            'prenom' => $user->prenom ?? '',
            'motif' => $bon->motif,
            'numero_bon' => $bon->id,
            'type' => $bon->statut,
        ]);

        return $bon;
    }

    /**
     * Génère le PDF d'un bon
     */
    private function generateBonPdf(Bon $bon, array $data): void
    {
        $pdf = Pdf::loadView('pdf.bon', $data);
        Storage::disk('public')->put($bon->fichier_pdf, $pdf->output());
    }

    /**
     * Met à jour la quantité et l'état de l'équipement après affectation
     */
    private function updateEquipementAfterAffectation(Equipement $equipement, int $quantite): void
    {
        // Le stock total reste inchangé.
        // Le stock disponible est recalculé dynamiquement via les affectations actives
        // et les pannes non résolues.
    }

    /**
     * Assigne automatiquement les équipements d'une demande à l'employé
     */
    private function assignEquipmentsFromDemande(Demande $demande, array $quantitesAAffecter = [], array $datesRetour = []): array
    {
        $user = Auth::user();
        $demande->loadMissing(['equipements', 'affectations']);
        $equipementsData = $demande->equipements;

        if ($equipementsData->isEmpty()) {
            return [
                'pdf_path' => null,
                'assigned_total' => 0,
                'bon' => null,
                'affectations_details' => [],
            ];
        }

        $affectationsDetails = [];
        $employe_id = $demande->user_id;
        $quantitesReservees = [];
        $assignedTotal = 0;

        foreach ($equipementsData as $equipement) {
            $quantiteDemandee = (int) ($equipement->pivot->nbr_equipement ?? 1);
            $quantiteRestante = $demande->getQuantiteRestantePourEquipement($equipement->id, $quantiteDemandee);

            if ($quantiteRestante === 0) {
                continue;
            }

            $quantite = (int) ($quantitesAAffecter[$equipement->id] ?? 0);
            $rawDate = $datesRetour[$equipement->id] ?? null;

            if ($quantite === 0) {
                continue;
            }

            if ($quantite > $quantiteRestante) {
                throw new Exception(sprintf(
                    'La quantité à affecter pour « %s » dépasse le restant à servir (%d).',
                    $equipement->nom,
                    $quantiteRestante
                ));
            }

            $this->validateAffectationAvailability(
                $equipement,
                $quantite,
                $quantitesReservees[$equipement->id] ?? 0
            );

            // Créer l'affectation
            Affectation::create([
                'equipement_id' => $equipement->id,
                'user_id' => $employe_id,
                'demande_id' => $demande->id,
                'date_retour' => $rawDate ?: null,
                'created_by' => $user->nom.' '.$user->prenom,
                'quantite_affectee' => $quantite,
                'statut' => 'active',
            ]);

            $quantitesReservees[$equipement->id] = ($quantitesReservees[$equipement->id] ?? 0) + $quantite;
            $assignedTotal += $quantite;

            $affectationsDetails[] = [
                'nom' => $equipement->nom,
                'reference' => $equipement->reference ?? '',
                'quantite' => $quantite,
                'date_retour' => $rawDate ?: null,
            ];
        }

        if ($assignedTotal === 0) {
            throw new Exception('Aucune quantité n’a été affectée. Veuillez saisir au moins une quantité à servir.');
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

        $this->generateBonPdf($bon, [
            'date' => now()->format('d/m/Y'),
            'nom' => $employe->nom ?? 'Employé',
            'prenom' => $employe->prenom ?? '',
            'motif' => $demande->motif ?? 'Affectation via demande approuvée',
            'numero_bon' => $bon->id,
            'type' => $bon->statut,
            'equipements' => $affectationsDetails,
        ]);

        return [
            'pdf_path' => $pdfPath,
            'assigned_total' => $assignedTotal,
            'bon' => $bon,
            'affectations_details' => $affectationsDetails,
        ];
    }

    /**
     * Valide la disponibilité et la quantité avant affectation
     */
    private function validateAffectationAvailability(Equipement $equipement, int $quantite, int $quantiteReservee = 0): void
    {
        if ($quantite <= 0) {
            throw new Exception('La quantité à affecter doit être supérieure à zéro.');
        }

        $quantiteDisponible = max(0, $equipement->getQuantiteDisponible() - $quantiteReservee);

        if ($quantiteDisponible < $quantite) {
            throw new Exception(sprintf(
                "Quantité insuffisante pour l'équipement « %s » (disponible : %d, demandée : %d).",
                $equipement->nom,
                $quantiteDisponible,
                $quantite
            ));
        }
    }

    /**
     * Fusionne les lignes strictement identiques d'une affectation directe.
     * Même équipement + même date de retour => une seule affectation.
     * Même équipement + dates différentes => affectations séparées.
     */
    private function normalizeDirectAffectationLines(array $equipements, array $quantites, array $datesRetour = []): array
    {
        $groupedLines = [];
        $orderedKeys = [];

        foreach ($equipements as $index => $equipementId) {
            $equipementId = (int) $equipementId;
            $quantite = (int) ($quantites[$index] ?? 0);
            $dateRetour = $datesRetour[$index] ?? null;
            $dateRetour = $dateRetour !== null && $dateRetour !== '' ? $dateRetour : null;

            if ($equipementId <= 0 || $quantite <= 0) {
                continue;
            }

            $groupKey = $equipementId.'|'.($dateRetour ?? 'sans-date');

            if (! array_key_exists($groupKey, $groupedLines)) {
                $groupedLines[$groupKey] = [
                    'equipement_id' => $equipementId,
                    'quantite' => 0,
                    'date_retour' => $dateRetour,
                ];
                $orderedKeys[] = $groupKey;
            }

            $groupedLines[$groupKey]['quantite'] += $quantite;
        }

        return array_map(
            fn (string $key) => $groupedLines[$key],
            $orderedKeys
        );
    }
}
