<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Demande;
use App\Services\DashboardCacheService;

final class DemandeObserver
{
    public function __construct(
        private readonly DashboardCacheService $dashboardCacheService,
    ) {}

    public function created(Demande $demande): void
    {
        $this->dashboardCacheService->forgetEmployeeMetrics((int) $demande->user_id);
    }

    public function updated(Demande $demande): void
    {
        $this->dashboardCacheService->forgetEmployeeMetrics((int) $demande->user_id);
    }

    public function deleted(Demande $demande): void
    {
        $this->dashboardCacheService->forgetEmployeeMetrics((int) $demande->user_id);
    }
}
