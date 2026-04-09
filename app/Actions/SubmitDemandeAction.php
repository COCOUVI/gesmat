<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\DemandeSubmitted;
use App\Models\Demande;
use App\Models\EquipementDemandé;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class SubmitDemandeAction
{
    /**
     * @param  array{lieu: string, motif: string, equipements: array<int, int|string>, quantites: array<int, int|string>}  $validated
     */
    public function handle(User $user, array $validated): Demande
    {
        /** @var Demande $demande */
        $demande = DB::transaction(function () use ($user, $validated): Demande {
            $demande = Demande::create([
                'lieu' => $validated['lieu'],
                'motif' => $validated['motif'],
                'user_id' => $user->id,
                'statut' => 'en_attente',
            ]);

            $quantitesParEquipement = [];

            foreach ($validated['equipements'] as $index => $equipementId) {
                $quantite = (int) ($validated['quantites'][$index] ?? 0);
                $quantitesParEquipement[(int) $equipementId] = ($quantitesParEquipement[(int) $equipementId] ?? 0) + $quantite;
            }

            foreach ($quantitesParEquipement as $equipementId => $quantite) {
                $equipementDemande = new EquipementDemandé();
                $equipementDemande->demande_id = $demande->id;
                $equipementDemande->equipement_id = $equipementId;
                $equipementDemande->nbr_equipement = $quantite;
                $equipementDemande->save();
            }

            event(new \App\Events\DemandeSubmitted($demande->fresh(['user', 'equipements'])));

            return $demande;
        });

        return $demande;
    }
}
