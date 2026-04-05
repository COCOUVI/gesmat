<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Demande;

final class DemandeController extends Controller
{
    public function index()
    {
        $demandes = Demande::with(['user', 'equipements'])->where('statut', 'en_attente')->latest()->get();

        // $demandes = Demande::with('equipements')->where("statut", "=", "en_attente")->latest()->get();
        return view('gestionnaire.demandes.index', ['demandes' => $demandes]);

    }
}
