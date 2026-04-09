<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EquipementStockChanged;
use App\Models\Equipement;
use App\Services\WorkflowNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendCriticalStockAlert implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function handle(EquipementStockChanged $event): void
    {
        $equipement = Equipement::query()
            ->with('categorie')
            ->find($event->equipementId);

        if (! $equipement instanceof Equipement) {
            return;
        }

        $this->workflowNotificationService->notifyCriticalStockAlertIfNeeded(
            $equipement,
            $event->previousAvailable,
            $event->previousThreshold
        );
    }
}
