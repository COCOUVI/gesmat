<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PanneReplacementCompleted;
use App\Services\WorkflowNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendPanneReplacementNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function handle(PanneReplacementCompleted $event): void
    {
        $this->workflowNotificationService->notifyPanneReplacement(
            $event->panne->fresh(['equipement', 'affectation.user', 'user']),
            $event->replacementQuantity,
            $event->bon
        );
    }
}
