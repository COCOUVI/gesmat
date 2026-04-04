<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Affectation;
use App\Models\Bon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class EquipmentReturned implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Affectation $affectation,
        public readonly int $healthyReturned,
        public readonly int $brokenReturned,
        public readonly ?Bon $bon = null,
    ) {}
}
