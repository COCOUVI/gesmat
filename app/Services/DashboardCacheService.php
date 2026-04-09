<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

final class DashboardCacheService
{
    public function adminMetricsKey(): string
    {
        return 'dashboard:admin:metrics:v1';
    }

    public function employeeMetricsKey(int $userId): string
    {
        return sprintf('dashboard:employee:metrics:user:%d:v1', $userId);
    }

    public function forgetAdminMetrics(): void
    {
        Cache::forget($this->adminMetricsKey());
    }

    public function forgetEmployeeMetrics(int $userId): void
    {
        Cache::forget($this->employeeMetricsKey($userId));
    }
}
