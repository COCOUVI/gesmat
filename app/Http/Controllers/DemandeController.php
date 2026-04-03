<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Demande;

final class DemandeController extends Controller
{
    public function index()
    {
        $demandes = Demande::with(['user', 'equipements'])->where('statut', 'en_attente')->orderBy('created_at', 'desc')->get();

        // $demandes = Demande::with('equipements')->where("statut", "=", "en_attente")->latest()->get();
        return view('gestionnaire.demandes.index', compact('demandes'));

    }
}
