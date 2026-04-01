<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categorie - Modèle pour la gestion des catégories d'équipements
 *
 * Attributs:
 * - nom: string (unique)
 */
final class Categorie extends Model
{
    protected $fillable = ['nom'];

    protected $table = 'categories';

    /**
     * Relation avec les équipements de cette catégorie
     */
    public function equipements(): HasMany
    {
        return $this->hasMany(Equipement::class);
    }
}
