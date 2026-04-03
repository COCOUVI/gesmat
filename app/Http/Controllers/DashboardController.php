<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Equipement;
use App\Models\User;

final class DashboardController extends Controller
{
    //     public function dashboard()
    // {
    //     $totalEquipements = Equipement::count();
    //     $equipementsAffectes = Affectation::distinct('equipement_id')->count('equipement_id');
    //     $utilisateursActifs = User::where('statut', 'actif')->count(); // modifie le champ 'statut' si besoin

    //     return view('gestionnaire.homedash', compact(
    //         'totalEquipements',
    //         'equipementsAffectes',
    //         'utilisateursActifs'
    //     ));
    // }
    public function index()
    {
        $totalEquipements = Equipement::count(); // ou ta logique ici

        return view('gestionnaire.homedash', [
            'totalEquipements' => $totalEquipements,
        ]);
    }
}
