<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categorie;
use App\Models\Equipement;


class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::all();
        return view('gestionnaire.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('gestionnaire.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255'
        ]);

        Categorie::create($validated);

        return redirect()->route('gestionnaire.categories.list')
                         ->with('success', 'Catégorie ajoutée avec succès.');
    }

    public function edit($id)
    {
        $categorie = Categorie::findOrFail($id);
        return view('gestionnaire.categories.edit', compact('categorie'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255'
        ]);

        $categorie = Categorie::findOrFail($id);
        $categorie->update($validated);

        return redirect()->route('gestionnaire.categories.list')
                         ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();

        return redirect()->route('gestionnaire.categories.list')
                         ->with('success', 'Catégorie supprimée.');
        }
    public function equipements()
    {
        return $this->hasMany(Equipement::class);
    }
}
