<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EquipmentReturned;
use App\Services\WorkflowNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendEquipmentReturnedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function handle(EquipmentReturned $event): void
    {
        $this->workflowNotificationService->notifyEquipmentReturned(
            $event->affectation->fresh(['user', 'collaborateurExterne', 'equipement', 'pannes']),
            $event->healthyReturned,
            $event->brokenReturned,
            $event->bon
        );
    }
}
