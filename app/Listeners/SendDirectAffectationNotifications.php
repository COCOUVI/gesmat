<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DirectAffectationCreated;
use App\Services\WorkflowNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendDirectAffectationNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {}

    public function handle(DirectAffectationCreated $event): void
    {
        $this->workflowNotificationService->notifyDirectAffectation(
            $event->employee->fresh(),
            $event->motif,
            $event->affectationsDetails,
            $event->bon
        );
    }
}
