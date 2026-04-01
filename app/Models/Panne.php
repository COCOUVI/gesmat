<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Panne - Modèle pour la gestion des pannes d'équipements et du suivi de stock
 *
 * Attributs:
 * - equipement_id: foreignId (équipement concerné)
 * - affectation_id: foreignId (affectation où la panne a été signalée, optional)
 * - user_id: foreignId (employé signalant la panne)
 * - quantite: integer (nombre d'unités affectées par la panne)
 * - description: text (description de la panne)
 * - statut: enum(en_attente, resolu)
 * - created_at, updated_at: timestamps
 */
final class Panne extends Model
{
    protected $fillable = [
        'equipement_id',
        'affectation_id',
        'user_id',
        'quantite',
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
     * Relation avec l'affectation (si la panne a été signalée sur une affectation)
     */
    public function affectation(): BelongsTo
    {
        return $this->belongsTo(Affectation::class);
    }

    /**
     * Relation avec l'utilisateur ayant signalé la panne
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour récupérer les pannes non résolues
     */
    public function scopeNonResolues($query)
    {
        return $query->where('statut', '!=', 'resolu');
    }

    /**
     * Scope pour récupérer les pannes en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour récupérer les pannes résolues
     */
    public function scopeResolues($query)
    {
        return $query->where('statut', 'resolu');
    }
}
