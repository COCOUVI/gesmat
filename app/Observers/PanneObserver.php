<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Panne;
use App\Services\CriticalStockMonitor;

final class PanneObserver
{
    public function __construct(
        private readonly CriticalStockMonitor $criticalStockMonitor,
    ) {}

    public function created(Panne $panne): void
    {
        $this->criticalStockMonitor->monitorPanneCreated($panne);
    }

    public function updated(Panne $panne): void
    {
        $this->criticalStockMonitor->monitorPanneUpdated($panne);
    }

    public function deleted(Panne $panne): void
    {
        $this->criticalStockMonitor->monitorPanneDeleted($panne);
    }
}
