<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Equipement;
use App\Services\CriticalStockMonitor;

final class EquipementObserver
{
    public function __construct(
        private readonly CriticalStockMonitor $criticalStockMonitor,
    ) {}

    public function created(Equipement $equipement): void
    {
        $this->criticalStockMonitor->monitorEquipementCreated($equipement);
    }

    public function updated(Equipement $equipement): void
    {
        $this->criticalStockMonitor->monitorEquipementUpdated($equipement);
    }
}
