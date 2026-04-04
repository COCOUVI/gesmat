<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Affectation;
use App\Models\Demande;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class CancelAffectationAction
{
    /**
     * @return array{equipement_nom: string, demande: Demande|null}
     */
    public function handle(int $affectationId): array
    {
        /** @var array{equipement_nom: string, demande: Demande|null} $result */
        $result = DB::transaction(function () use ($affectationId): array {
            $affectation = Affectation::with(['equipement', 'user', 'demande'])
                ->findOrFail($affectationId);

            $affectation->setAttribute('pannes_count', $affectation->pannes()->count());

            if (! $affectation->peutEtreAnnulee()) {
                throw new Exception($affectation->getMotifBlocageAnnulation() ?? 'Cette affectation ne peut pas être annulée.');
            }

            $demande = $affectation->demande;
            $equipementNom = $affectation->equipement->nom ?? 'Équipement';

            Affectation::whereKey($affectation->id)->delete();

            if ($demande) {
                $demande->refresh()->load(['equipements', 'affectations']);
                $demande->update([
                    'statut' => $demande->estEntierementServie() ? 'acceptee' : 'en_attente',
                ]);
                $demande->refresh();
            }

            return [
                'equipement_nom' => $equipementNom,
                'demande' => $demande,
            ];
        });

        return $result;
    }
}
