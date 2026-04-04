<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Panne;
use App\Services\CriticalStockMonitor;
use App\Services\DashboardCacheService;

final class PanneObserver
{
    public function __construct(
        private readonly CriticalStockMonitor $criticalStockMonitor,
        private readonly DashboardCacheService $dashboardCacheService,
    ) {}

    public function created(Panne $panne): void
    {
        $this->criticalStockMonitor->monitorPanneCreated($panne);
        $this->dashboardCacheService->forgetAdminMetrics();

        if ($panne->user_id !== null) {
            $this->dashboardCacheService->forgetEmployeeMetrics((int) $panne->user_id);
        }
    }

    public function updated(Panne $panne): void
    {
        $this->criticalStockMonitor->monitorPanneUpdated($panne);
        $this->dashboardCacheService->forgetAdminMetrics();

        if ($panne->user_id !== null) {
            $this->dashboardCacheService->forgetEmployeeMetrics((int) $panne->user_id);
        }
    }

    public function deleted(Panne $panne): void
    {
        $this->criticalStockMonitor->monitorPanneDeleted($panne);
        $this->dashboardCacheService->forgetAdminMetrics();

        if ($panne->user_id !== null) {
            $this->dashboardCacheService->forgetEmployeeMetrics((int) $panne->user_id);
        }
    }
}
