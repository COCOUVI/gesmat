<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Demande - Modèle pour la gestion des demandes d'équipements
 *
 * Attributs:
 * - lieu: string (nullable)
 * - motif: string
 * - statut: enum(en_attente, acceptee, rejetee, assignée)
 * - user_id: foreignId (employé demandeur)
 * - gestionnaire_id: foreignId (nullable, gestionnaire assigné)
 */
class Demande extends Model
{
    protected $fillable = ['lieu', 'motif', 'statut', 'user_id', 'gestionnaire_id'];

    /**
     * Relation avec l'employé (celui qui a fait la demande)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les équipements demandés via table pivot equipement_demandés
     */
    public function equipements(): BelongsToMany
    {
        return $this->belongsToMany(Equipement::class, 'equipement_demandés')
            ->withPivot('nbr_equipement')
            ->withTimestamps();
    }

    /**
     * Relation avec le gestionnaire assigné
     */
    public function gestionnaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestionnaire_id');
    }
}
