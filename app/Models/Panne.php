<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Panne - Modèle pour la gestion des pannes d'équipements
 *
 * Attributs:
 * - equipement_id: foreignId
 * - user_id: foreignId (employé signalant la panne)
 * - description: text
 * - statut: enum(en_attente, resolu)
 */
final class Panne extends Model
{
    protected $fillable = [
        'equipement_id',
        'user_id',
        'description',
        'statut',
    ];

    /**
     * Relation avec l'équipement en panne
     */
    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    /**
     * Relation avec l'utilisateur ayant signalé la panne
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
