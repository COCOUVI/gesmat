<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Affectation - Modèle pour la gestion des affectations d'équipements
 *
 * Attributs:
 * - equipement_id: foreignId
 * - user_id: foreignId
 * - date_retour: datetime (nullable)
 * - quantite_affectee: integer
 * - created_by: string (nom du créateur)
 */
final class Affectation extends Model
{
    protected $fillable = [
        'equipement_id',
        'user_id',
        'date_retour',
        'quantite_affectee',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_retour' => 'datetime',
        ];
    }

    /**
     * Relation avec l'équipement affecté
     */
    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    /**
     * Relation avec l'utilisateur ayant l'affectation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
