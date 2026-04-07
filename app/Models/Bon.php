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
        'interlocuteur_type',
        'interlocuteur_id',
        'interlocuteur_nom_libre',
    ];

    /**
     * Relation avec l'utilisateur (employé)
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec le collaborateur externe
     *
     * @return BelongsTo<CollaborateurExterne, $this>
     */
    public function collaborateurExterne(): BelongsTo
    {
        return $this->belongsTo(CollaborateurExterne::class, 'collaborateur_externe_id');
    }

    /**
     * Relation avec les équipements du bon
     *
     * @return BelongsToMany<Equipement, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function equipements(): BelongsToMany
    {
        return $this->belongsToMany(Equipement::class, 'bon_equipement')
            ->withPivot('quantite')
            ->withTimestamps();
    }

    /**
     * Récupère l'interlocuteur (user, collaborateur externe ou libre)
     */
    public function getInterlocuteur(): ?Model
    {
        return match ($this->interlocuteur_type) {
            'user' => User::find($this->interlocuteur_id),
            'collaborateur_externe' => CollaborateurExterne::find($this->interlocuteur_id),
            default => null,
        };
    }

    /**
     * Récupère le nom complet de l'interlocuteur
     */
    public function getInterlocuteurNom(): string
    {
        return match ($this->interlocuteur_type) {
            'user' => ($this->getInterlocuteur()?->nom ?? '').' '.($this->getInterlocuteur()?->prenom ?? ''),
            'collaborateur_externe' => $this->getInterlocuteur()?->nom ?? '',
            'libre' => $this->interlocuteur_nom_libre ?? 'Inconnu',
            default => 'Inconnu',
        };
    }
}
