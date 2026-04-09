<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class StoreInternalPanneAction
{
    /**
     * @param  array{equipement_id: int|string, quantite: int|string, description: string}  $validated
     */
    public function handle(User $actor, array $validated): Panne
    {
        /** @var Panne $panne */
        $panne = DB::transaction(function () use ($actor, $validated): Panne {
            $equipement = Equipement::findOrFail((int) $validated['equipement_id']);
            $quantite = (int) $validated['quantite'];

            if ($quantite > $equipement->getQuantiteDisponible()) {
                throw new Exception(sprintf(
                    'Vous ne pouvez déclarer en panne interne que %d unité(s) pour « %s ».',
                    $equipement->getQuantiteDisponible(),
                    $equipement->nom
                ));
            }

            return Panne::create([
                'equipement_id' => $equipement->id,
                'affectation_id' => null,
                'user_id' => $actor->id,
                'quantite' => $quantite,
                'quantite_retournee_stock' => 0,
                'quantite_resolue' => 0,
                'description' => $validated['description'],
                'statut' => 'en_attente',
            ]);
        });

        return $panne;
    }
}
