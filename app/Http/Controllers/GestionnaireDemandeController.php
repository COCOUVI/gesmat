<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Http\Request;

final class GestionnaireDemandeController extends Controller
{
    // Afficher la liste des demandes en attente
    public function index()
    {
        $demandes = Demande::with(['user', 'equipements'])
            ->where('statut', 'en_attente') // Filtrer les demandes non traitées
            ->get();

        $gestionnaires = User::where('role', 'gestionnaire')->get(); // Liste des gestionnaires pour l'assignation

        return view('gestionnaire.demandes.index', compact('demandes', 'gestionnaires'));
    }

    // Assigner une demande à un gestionnaire
    public function assigner(Request $request, Demande $demande)
    {
        $request->validate([
            'gestionnaire_id' => 'required|exists:users,id',
        ]);

        $demande->update([
            'statut' => 'en_cours',
            'gestionnaire_id' => $request->gestionnaire_id, // Ajoutez cette colonne si elle n'existe pas
        ]);

        return redirect()->back()->with('success', 'Demande assignée avec succès !');
    }
}
