<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Equipement;
use App\Services\CriticalStockMonitor;
use App\Services\DashboardCacheService;

final class EquipementObserver
{
    public function __construct(
        private readonly CriticalStockMonitor $criticalStockMonitor,
        private readonly DashboardCacheService $dashboardCacheService,
    ) {}

    public function created(Equipement $equipement): void
    {
        $this->criticalStockMonitor->monitorEquipementCreated($equipement);
        $this->dashboardCacheService->forgetAdminMetrics();
    }

    public function updated(Equipement $equipement): void
    {
        $this->criticalStockMonitor->monitorEquipementUpdated($equipement);
        $this->dashboardCacheService->forgetAdminMetrics();
    }
}
