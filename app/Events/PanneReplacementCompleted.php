<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Bon;
use App\Models\Panne;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PanneReplacementCompleted implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Panne $panne,
        public readonly int $replacementQuantity,
        public readonly ?Bon $bon = null,
    ) {}
}
