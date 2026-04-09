<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Bon;
use App\Models\Demande;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class DemandeServed implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;
    /**
     * @param  array<int, array{nom: string, quantite: int, date_retour: ?string}>  $affectationsDetails
     */
    public function __construct(
        public readonly Demande $demande,
        public readonly array $affectationsDetails,
        public readonly ?Bon $bon = null,
    ) {}
}
