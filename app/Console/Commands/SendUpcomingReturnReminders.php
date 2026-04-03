<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Affectation;
use App\Services\WorkflowNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class SendUpcomingReturnReminders extends Command
{
    protected $signature = 'app:send-upcoming-return-reminders';

    protected $description = 'Envoie les rappels de retour proches aux employés concernés.';

    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $today = Carbon::today();
        $limitDate = $today->copy()->addDays(3);
        $sentCount = 0;

        $affectations = Affectation::query()
            ->with(['user', 'equipement'])
            ->active()
            ->whereNotNull('date_retour')
            ->whereDate('date_retour', '>=', $today)
            ->whereDate('date_retour', '<=', $limitDate)
            ->get();

        foreach ($affectations as $affectation) {
            $cacheKey = sprintf(
                'return-reminder:%d:%s:%s',
                $affectation->id,
                optional($affectation->date_retour)->format('Y-m-d'),
                $today->format('Y-m-d')
            );

            if (! Cache::add($cacheKey, true, now()->endOfDay())) {
                continue;
            }

            $this->workflowNotificationService->notifyUpcomingReturnReminder($affectation);
            $sentCount++;
        }

        $this->info(sprintf('%d rappel(s) de retour envoyé(s).', $sentCount));

        return self::SUCCESS;
    }
}
