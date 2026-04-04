<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PanneResolved;
use App\Services\WorkflowNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendPanneResolvedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function handle(PanneResolved $event): void
    {
        $this->workflowNotificationService->notifyPanneResolved(
            $event->panne->fresh(['equipement', 'affectation.user', 'user']),
            $event->resolvedQuantity
        );
    }
}
