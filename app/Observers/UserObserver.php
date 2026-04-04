<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\DashboardCacheService;

final class UserObserver
{
    public function __construct(
        private readonly DashboardCacheService $dashboardCacheService,
    ) {}

    public function created(User $user): void
    {
        $this->dashboardCacheService->forgetAdminMetrics();
    }

    public function updated(User $user): void
    {
        $this->dashboardCacheService->forgetAdminMetrics();
        $this->dashboardCacheService->forgetEmployeeMetrics((int) $user->id);
    }

    public function deleted(User $user): void
    {
        $this->dashboardCacheService->forgetAdminMetrics();
        $this->dashboardCacheService->forgetEmployeeMetrics((int) $user->id);
    }
}
