<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Affectation;
use App\Services\CriticalStockMonitor;

final class AffectationObserver
{
    public function __construct(
        private readonly CriticalStockMonitor $criticalStockMonitor,
    ) {}

    public function created(Affectation $affectation): void
    {
        $this->criticalStockMonitor->monitorAffectationCreated($affectation);
    }

    public function updated(Affectation $affectation): void
    {
        $this->criticalStockMonitor->monitorAffectationUpdated($affectation);
    }

    public function deleted(Affectation $affectation): void
    {
        $this->criticalStockMonitor->monitorAffectationDeleted($affectation);
    }
}
