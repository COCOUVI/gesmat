<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Relation avec les pannes liées à cette affectation
     */
    public function pannes(): HasMany
    {
        return $this->hasMany(Panne::class);
    }

    /**
     * Vérifie si cette affectation a des pannes non résolues
     */
    public function aPannesNonResolues(): bool
    {
        return $this->pannes()
            ->where('statut', '!=', 'resolu')
            ->exists();
    }

    /**
     * Obtient le nombre de pannes non résolues
     */
    public function getQuantitePannesNonResolues(): int
    {
        return (int) $this->pannes()
            ->where('statut', '!=', 'resolu')
            ->sum('quantite');
    }
}
