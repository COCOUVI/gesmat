<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Equipement;
use Illuminate\Http\Request;

final class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::all();

        return view('gestionnaire.categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('gestionnaire.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
        ]);

        Categorie::create($validated);

        return to_route('gestionnaire.categories.list')
            ->with('success', 'Catégorie ajoutée avec succès.');
    }

    public function edit($id)
    {
        $categorie = Categorie::findOrFail($id);

        return view('gestionnaire.categories.edit', ['categorie' => $categorie]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
        ]);

        $categorie = Categorie::findOrFail($id);
        $categorie->update($validated);

        return to_route('gestionnaire.categories.list')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();

        return to_route('gestionnaire.categories.list')
            ->with('success', 'Catégorie supprimée.');
    }

    public function equipements()
    {
        return $this->hasMany(Equipement::class);
    }
}
