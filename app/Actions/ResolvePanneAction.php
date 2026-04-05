<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Panne;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class ResolvePanneAction
{
    /**
     * @return array{panne: Panne, resolved_quantity: int}
     */
    public function handle(Panne $panne, int $quantiteResolue): array
    {
        /** @var array{panne: Panne, resolved_quantity: int} $result */
        $result = DB::transaction(function () use ($panne, $quantiteResolue): array {
            $panne->load(['equipement', 'affectation']);

            $quantiteResolvable = $panne->getQuantiteResolvable();

            throw_if($quantiteResolvable <= 0, Exception::class, 'Aucune quantité n’est encore disponible pour résolution sur cette panne.');

            if ($quantiteResolue > $quantiteResolvable) {
                throw new Exception(sprintf(
                    'Vous ne pouvez résoudre que %d unité(s) pour cette panne.',
                    $quantiteResolvable
                ));
            }

            $panne->quantite_resolue = $panne->getQuantiteResolue() + $quantiteResolue;
            $panne->statut = $panne->getQuantiteNonResolue() === 0 ? 'resolu' : 'en_attente';
            $panne->save();

            return [
                'panne' => $panne->fresh(['equipement', 'affectation.user', 'user']),
                'resolved_quantity' => $quantiteResolue,
            ];
        });

        return $result;
    }
}
