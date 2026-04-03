<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Bon - Modèle pour la gestion des bons d'entrée/sortie
 *
 * Attributs:
 * - user_id: foreignId (employé ou collaborateur associé)
 * - collaborateur_externe_id: foreignId (nullable, pour bons collaborateurs)
 * - motif: string
 * - statut: enum(entrée, sortie)
 * - fichier_pdf: string (nullable, chemin du PDF généré)
 */
final class Bon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'collaborateur_externe_id',
        'motif',
        'statut',
        'fichier_pdf',
    ];

    /**
     * Relation avec l'utilisateur (employé)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec le collaborateur externe
     */
    public function collaborateurExterne(): BelongsTo
    {
        return $this->belongsTo(CollaborateurExterne::class, 'collaborateur_externe_id');
    }

    /**
     * Relation avec les équipements du bon
     */
    public function equipements(): BelongsToMany
    {
        return $this->belongsToMany(Equipement::class, 'bon_equipement')
            ->withPivot('quantite')
            ->withTimestamps();
    }
}
