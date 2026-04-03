<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\EquipementStockChanged;
use App\Models\Affectation;
use App\Models\Equipement;
use App\Models\Panne;

final class CriticalStockMonitor
{
    public function monitorEquipementCreated(Equipement $equipement): void
    {
        event(new EquipementStockChanged($equipement->id));
    }

    public function monitorEquipementUpdated(Equipement $equipement): void
    {
        $freshEquipement = $equipement->fresh();

        if (! $freshEquipement instanceof Equipement) {
            return;
        }

        $previousAvailable = $freshEquipement->getQuantiteDisponible()
            - ((int) $equipement->quantite - (int) $equipement->getOriginal('quantite'));

        event(new EquipementStockChanged(
            $freshEquipement->id,
            max(0, $previousAvailable),
            max(0, (int) $equipement->getOriginal('seuil_critique'))
        ));
    }

    public function monitorAffectationCreated(Affectation $affectation): void
    {
        $this->dispatchForAffectation(
            $affectation,
            $this->getAffectationActiveQuantity($affectation->quantite_affectee, $affectation->quantite_retournee, $affectation->statut),
            0
        );
    }

    public function monitorAffectationUpdated(Affectation $affectation): void
    {
        $this->dispatchForAffectation(
            $affectation,
            $this->getAffectationActiveQuantity($affectation->quantite_affectee, $affectation->quantite_retournee, $affectation->statut),
            $this->getAffectationActiveQuantity(
                (int) $affectation->getOriginal('quantite_affectee'),
                (int) $affectation->getOriginal('quantite_retournee'),
                (string) $affectation->getOriginal('statut')
            )
        );
    }

    public function monitorAffectationDeleted(Affectation $affectation): void
    {
        $this->dispatchForAffectation(
            $affectation,
            0,
            $this->getAffectationActiveQuantity(
                (int) $affectation->getOriginal('quantite_affectee'),
                (int) $affectation->getOriginal('quantite_retournee'),
                (string) $affectation->getOriginal('statut')
            )
        );
    }

    public function monitorPanneCreated(Panne $panne): void
    {
        $this->dispatchForPanne(
            $panne,
            $this->getPanneInternalQuantity(
                $panne->affectation_id,
                $panne->quantite,
                $panne->quantite_retournee_stock,
                $panne->quantite_resolue,
                $panne->statut
            ),
            0
        );
    }

    public function monitorPanneUpdated(Panne $panne): void
    {
        $this->dispatchForPanne(
            $panne,
            $this->getPanneInternalQuantity(
                $panne->affectation_id,
                $panne->quantite,
                $panne->quantite_retournee_stock,
                $panne->quantite_resolue,
                $panne->statut
            ),
            $this->getPanneInternalQuantity(
                $panne->getOriginal('affectation_id') !== null ? (int) $panne->getOriginal('affectation_id') : null,
                (int) $panne->getOriginal('quantite'),
                (int) $panne->getOriginal('quantite_retournee_stock'),
                (int) $panne->getOriginal('quantite_resolue'),
                (string) $panne->getOriginal('statut')
            )
        );
    }

    public function monitorPanneDeleted(Panne $panne): void
    {
        $this->dispatchForPanne(
            $panne,
            0,
            $this->getPanneInternalQuantity(
                $panne->getOriginal('affectation_id') !== null ? (int) $panne->getOriginal('affectation_id') : null,
                (int) $panne->getOriginal('quantite'),
                (int) $panne->getOriginal('quantite_retournee_stock'),
                (int) $panne->getOriginal('quantite_resolue'),
                (string) $panne->getOriginal('statut')
            )
        );
    }

    private function dispatchForAffectation(Affectation $affectation, int $currentActive, int $previousActive): void
    {
        $equipement = Equipement::query()->find($affectation->equipement_id);

        if (! $equipement instanceof Equipement) {
            return;
        }

        $currentAvailable = $equipement->getQuantiteDisponible();
        $previousAvailable = $currentAvailable + $currentActive - $previousActive;

        event(new EquipementStockChanged(
            $equipement->id,
            max(0, $previousAvailable),
            max(0, (int) $equipement->seuil_critique)
        ));
    }

    private function dispatchForPanne(Panne $panne, int $currentInternal, int $previousInternal): void
    {
        $equipement = Equipement::query()->find($panne->equipement_id);

        if (! $equipement instanceof Equipement) {
            return;
        }

        $currentAvailable = $equipement->getQuantiteDisponible();
        $previousAvailable = $currentAvailable + $currentInternal - $previousInternal;

        event(new EquipementStockChanged(
            $equipement->id,
            max(0, $previousAvailable),
            max(0, (int) $equipement->seuil_critique)
        ));
    }

    private function getAffectationActiveQuantity(int $quantiteAffectee, ?int $quantiteRetournee, ?string $statut): int
    {
        $returnedQuantity = (int) ($quantiteRetournee ?? 0);

        if ($returnedQuantity === 0 && $statut === 'retourné') {
            $returnedQuantity = $quantiteAffectee;
        }

        return max(0, $quantiteAffectee - $returnedQuantity);
    }

    private function getPanneInternalQuantity(?int $affectationId, int $quantite, ?int $quantiteRetourneeStock, ?int $quantiteResolue, ?string $statut): int
    {
        if ($statut === 'resolu') {
            return 0;
        }

        $returnedToStock = max(0, (int) ($quantiteRetourneeStock ?? 0));
        $resolvedQuantity = max(0, (int) ($quantiteResolue ?? 0));

        if ($affectationId === null) {
            return max(0, $quantite - $resolvedQuantity);
        }

        $initialQuantityAtEmployee = max(0, $quantite - $returnedToStock);
        $resolvedInternalQuantity = max(0, $resolvedQuantity - $initialQuantityAtEmployee);

        return max(0, $returnedToStock - $resolvedInternalQuantity);
    }
}
