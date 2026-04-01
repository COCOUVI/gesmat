<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Equipement - Modèle pour la gestion des équipements
 *
 * Attributs:
 * - nom: string
 * - etat: enum(disponible, usagé, en panne, réparé)
 * - marque: string
 * - description: longText
 * - date_acquisition: date
 * - quantite: integer
 * - image_path: string
 * - categorie_id: foreignId
 */
final class Equipement extends Model
{
    protected $fillable = ['nom', 'etat', 'marque', 'description', 'date_acquisition', 'image_path', 'categorie_id', 'quantite'];

    /**
     * Relation avec la catégorie
     */
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    /**
     * Relation avec les demandes via table pivot equipement_demandés
     */
    public function demandes(): BelongsToMany
    {
        return $this->belongsToMany(Demande::class, 'equipement_demandés')
            ->withPivot('nbr_equipement')
            ->withTimestamps();
    }

    /**
     * Relation avec les utilisateurs via affectations
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'affectations')
            ->withPivot('date_retour', 'quantite_affectee', 'created_by');
    }

    /**
     * Relation avec les pannes
     */
    public function pannes(): HasMany
    {
        return $this->hasMany(Panne::class);
    }

    /**
     * Relation avec les affectations
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class);
    }
}
