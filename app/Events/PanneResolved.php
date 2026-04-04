<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Panne;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PanneResolved implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Panne $panne,
        public readonly int $resolvedQuantity,
    ) {}
}
