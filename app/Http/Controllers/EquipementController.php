<?php

namespace App\Http\Controllers;

use App\Enums\EquipementEtat;
use Illuminate\Http\Request;
use App\Models\Equipement;
use App\Models\Categorie;
use Illuminate\Support\Facades\Log;

/**
 * EquipementController - Gère les opérations CRUD sur les équipements
 * 
 * Responsabilités:
 * - Listing et recherche d'équipements
 * - Création et modification d'équipements
 * - Suppression d'équipements
 * - Gestion des images d'équipements
 * - Gestion des équipements en panne
 */
class EquipementController extends Controller
{
    /**
     * Affiche le formulaire d'ajout ou de modification
     */
    public function create()
    {
        $categories = Categorie::all();
        return view('gestionnaire.tools.addtool', compact('categories'));
    }

    /**
     * Affiche la liste des équipements avec pagination
     */
    public function index()
    {
        $equipements = Equipement::with('categorie')->paginate(10);
        return view('gestionnaire.tools.listtools', compact('equipements'));
    }

    /**
     * Affiche le formulaire de modification d'un équipement
     */
    public function edit($id)
    {
        $equipement = Equipement::findOrFail($id);
        $categories = Categorie::all();
        return view('gestionnaire.tools.addtool', compact('equipement', 'categories'));
    }

    /**
     * Enregistre un nouvel équipement avec validation et gestion d'image
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'etat' => 'required',
            'categorie_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string',
            'marque' => 'nullable|string',
            'quantite' => 'required|integer|min:1',
            'date_acquisition' => 'nullable|date',
            'image_path' => 'nullable|image|max:2048',
        ]);

        try {
            $imagePath = null;
            if ($request->hasFile('image_path')) {
                $imagePath = $this->storeImage($request);
            }

            $equipement = Equipement::create([
                'nom' => $validated['nom'],
                'etat' => $validated['etat'],
                'marque' => $validated['marque'] ?? null,
                'description' => $validated['description'] ?? null,
                'quantite' => $validated['quantite'],
                'date_acquisition' => $validated['date_acquisition'] ?? null,
                'categorie_id' => $validated['categorie_id'],
                'image_path' => $imagePath,
            ]);

            return redirect()->back()->with('success', 'Équipement ajouté avec succès !');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la création d'équipement : " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'ajout de l\'équipement.')
                ->withInput();
        }
    }

    /**
     * Met à jour un équipement existant
     */
    public function update(Request $request, $id)
    {
        $equipement = Equipement::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'etat' => 'required',
            'categorie_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string',
            'marque' => 'nullable|string',
            'quantite' => 'required|integer|min:0',
            'date_acquisition' => 'nullable|date',
            'image_path' => 'nullable|image|max:2048',
        ]);

        try {
            $data = $validated;
            
            if ($request->hasFile('image_path')) {
                $this->deleteImage($equipement->image_path);
                $data['image_path'] = $this->storeImage($request);
            }

            $equipement->update($data);

            return redirect()->route('gestionnaire.tools.list')
                ->with('success', 'Équipement modifié avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour d'équipement : " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la modification de l\'équipement.')
                ->withInput();
        }
    }

    /**
     * Supprime un équipement et son image
     */
    public function destroy($id)
    {
        try {
            $equipement = Equipement::findOrFail($id);

            if ($equipement->image_path) {
                $this->deleteImage($equipement->image_path);
            }

            $equipement->delete();

            return redirect()->back()->with('success', 'Équipement supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression d'équipement : " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de l\'équipement.');
        }
    }

    /**
     * Affiche les équipements en panne
     */
    public function showPanne()
    {
        $pannes = Equipement::where('etat', EquipementEtat::EN_PANNE->value)
            ->orderBy('created_at', 'desc')
            ->with('categorie')
            ->paginate(10);
        
        return view('gestionnaire.tools.pannelist', compact('pannes'));
    }

    // ============================================================================
    // MÉTHODES PRIVÉES - Utilitaires
    // ============================================================================

    /**
     * Stocke l'image de l'équipement localement et retourne le chemin
     */
    private function storeImage(Request $request): string
    {
        $file = $request->file('image_path');
        $imageName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $imageName);
        
        return 'uploads/' . $imageName;
    }

    /**
     * Supprime un fichier image s'il existe
     */
    private function deleteImage(?string $imagePath): void
    {
        if ($imagePath && file_exists(public_path($imagePath))) {
            @unlink(public_path($imagePath));
        }
    }
}
