<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bon - Modèle pour la gestion des bons d'entrée/sortie
 *
 * Attributs:
 * - user_id: foreignId (employé ou collaborateur associé)
 * - motif: string
 * - statut: enum(entrée, sortie)
 * - fichier_pdf: string (nullable, chemin du PDF généré)
 */
class Bon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'motif',
        'statut',
        'fichier_pdf'
    ];

    /**
     * Relation avec l'utilisateur (employé ou collaborateur)
     * Note: Le bon peut être lié soit à un User regular, soit à un CollaborateurExterne
     * selon le contexte (affectation vs bon externe)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
