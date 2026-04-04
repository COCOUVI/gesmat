<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Affectation;
use App\Services\CriticalStockMonitor;
use App\Services\DashboardCacheService;

final class AffectationObserver
{
    public function __construct(
        private readonly CriticalStockMonitor $criticalStockMonitor,
        private readonly DashboardCacheService $dashboardCacheService,
    ) {}

    public function created(Affectation $affectation): void
    {
        $this->criticalStockMonitor->monitorAffectationCreated($affectation);
        $this->dashboardCacheService->forgetAdminMetrics();

        if ($affectation->user_id !== null) {
            $this->dashboardCacheService->forgetEmployeeMetrics((int) $affectation->user_id);
        }
    }

    public function updated(Affectation $affectation): void
    {
        $this->criticalStockMonitor->monitorAffectationUpdated($affectation);
        $this->dashboardCacheService->forgetAdminMetrics();

        if ($affectation->user_id !== null) {
            $this->dashboardCacheService->forgetEmployeeMetrics((int) $affectation->user_id);
        }
    }

    public function deleted(Affectation $affectation): void
    {
        $this->criticalStockMonitor->monitorAffectationDeleted($affectation);
        $this->dashboardCacheService->forgetAdminMetrics();

        if ($affectation->user_id !== null) {
            $this->dashboardCacheService->forgetEmployeeMetrics((int) $affectation->user_id);
        }
    }
}
