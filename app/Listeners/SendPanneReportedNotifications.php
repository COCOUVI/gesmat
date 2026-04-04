<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PanneReported;
use App\Services\WorkflowNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendPanneReportedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function handle(PanneReported $event): void
    {
        $this->workflowNotificationService->notifyPanneReported(
            $event->panne->fresh(['user', 'equipement', 'affectation'])
        );
    }
}
