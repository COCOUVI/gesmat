<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\PanneReported;
use App\Models\Affectation;
use App\Models\Panne;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ReportPanneAction
{
    /**
     * @param  array{affectation_id: int|string, quantite: int|string, description: string}  $validated
     *
     * @throws ValidationException
     */
    public function handle(User $user, array $validated): Panne
    {
        /** @var Panne $panne */
        $panne = DB::transaction(function () use ($user, $validated): Panne {
            $affectation = Affectation::with('equipement')
                ->active()
                ->findOrFail((int) $validated['affectation_id']);

            if ($affectation->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'affectation_id' => 'Vous ne pouvez signaler une panne que sur vos propres affectations.',
                ]);
            }

            $quantiteAffectee = (int) $affectation->quantite_affectee;
            $quantiteEnPanneSignalee = (int) $affectation->pannes()
                ->where('statut', '!=', 'resolu')
                ->sum('quantite');
            $quantiteRestante = $quantiteAffectee - $quantiteEnPanneSignalee;
            $quantiteDemandee = (int) $validated['quantite'];

            if ($quantiteDemandee > $quantiteRestante) {
                throw ValidationException::withMessages([
                    'quantite' => sprintf(
                        'Vous ne pouvez signaler que %d équipement(s) en panne pour cette affectation (affecté: %d, déjà signalé: %d).',
                        $quantiteRestante,
                        $quantiteAffectee,
                        $quantiteEnPanneSignalee
                    ),
                ]);
            }

            $panne = Panne::create([
                'equipement_id' => $affectation->equipement->id,
                'affectation_id' => $affectation->id,
                'user_id' => $user->id,
                'quantite' => $quantiteDemandee,
                'description' => $validated['description'],
                'statut' => 'en_attente',
            ]);

            PanneReported::dispatch($panne->fresh(['user', 'equipement', 'affectation']));

            return $panne;
        });

        return $panne;
    }
}
