<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CollaborateurExterne extends Model
{
    use HasFactory;

    protected $table = 'collaborateur_externes';

    protected $fillable = [
        'nom',
        'prenom',
        'carte_chemin',
    ];

    /**
     * Relation avec les bons de ce collaborateur
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Bon, $this>
     */
    public function bons(): HasMany
    {
        return $this->hasMany(Bon::class, 'collaborateur_externe_id');
    }

    /**
     * Relation avec les affectations de ce collaborateur
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Affectation, $this>
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class, 'collaborateur_externe_id');
    }
}
