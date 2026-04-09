<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DemandeServed;
use App\Services\WorkflowNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendDemandeServedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function handle(DemandeServed $event): void
    {
        $this->workflowNotificationService->notifyDemandeServed(
            $event->demande->fresh(['user', 'equipements', 'affectations']),
            $event->affectationsDetails,
            $event->bon
        );
    }
}
