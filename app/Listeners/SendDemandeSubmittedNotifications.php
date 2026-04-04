<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DemandeSubmitted;
use App\Services\WorkflowNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendDemandeSubmittedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function handle(DemandeSubmitted $event): void
    {
        $this->workflowNotificationService->notifyDemandeSubmitted(
            $event->demande->fresh(['user', 'equipements'])
        );
    }
}
