<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
final class Demande extends Model
{
    protected $fillable = ['lieu', 'motif', 'statut', 'user_id', 'gestionnaire_id'];

    /**
     * Relation avec l'employé (celui qui a fait la demande)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les équipements demandés via table pivot equipement_demandés
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Equipement, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function equipements(): BelongsToMany
    {
        return $this->belongsToMany(Equipement::class, 'equipement_demandés')
            ->withPivot('nbr_equipement')
            ->withTimestamps();
    }

    /**
     * Relation avec le gestionnaire assigné
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function gestionnaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestionnaire_id');
    }

    /**
     * Affectations créées à partir de cette demande.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Affectation, $this>
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class);
    }

    /**
     * Retourne la quantité déjà servie pour un équipement donné.
     */
    public function getQuantiteServiePourEquipement(int $equipementId): int
    {
        $affectations = $this->relationLoaded('affectations')
            ? $this->affectations
            : $this->affectations()->get();

        return (int) $affectations
            ->where('equipement_id', $equipementId)
            ->sum('quantite_affectee');
    }

    /**
     * Retourne la quantité restante à servir pour un équipement demandé.
     */
    public function getQuantiteRestantePourEquipement(int $equipementId, int $quantiteDemandee): int
    {
        return max(0, $quantiteDemandee - $this->getQuantiteServiePourEquipement($equipementId));
    }

    /**
     * Indique si la demande a commencé à être servie.
     */
    public function estPartiellementServie(): bool
    {
        return $this->getQuantiteTotaleServie() > 0 && ! $this->estEntierementServie();
    }

    /**
     * Indique si toute la demande a été servie.
     */
    public function estEntierementServie(): bool
    {
        $equipements = $this->relationLoaded('equipements')
            ? $this->equipements
            : $this->equipements()->get();

        if ($equipements->isEmpty()) {
            return false;
        }

        return $equipements->every(function ($equipement) {
            $quantiteDemandee = (int) $equipement->pivot->nbr_equipement;

            return $this->getQuantiteRestantePourEquipement($equipement->id, $quantiteDemandee) === 0;
        });
    }

    /**
     * Retourne la quantité totale demandée.
     */
    public function getQuantiteTotaleDemandee(): int
    {
        $equipements = $this->relationLoaded('equipements')
            ? $this->equipements
            : $this->equipements()->get();

        return (int) $equipements->sum(fn ($equipement) => (int) $equipement->pivot->nbr_equipement);
    }

    /**
     * Retourne la quantité totale déjà servie.
     */
    public function getQuantiteTotaleServie(): int
    {
        $affectations = $this->relationLoaded('affectations')
            ? $this->affectations
            : $this->affectations()->get();

        return (int) $affectations->sum('quantite_affectee');
    }

    /**
     * Retourne un statut d'affichage métier sans dépendre uniquement du champ statut.
     */
    public function getStatutAffichage(): string
    {
        if ($this->statut === 'rejetee') {
            return 'rejetee';
        }

        if ($this->estEntierementServie()) {
            return 'acceptee';
        }

        if ($this->estPartiellementServie()) {
            return 'partiellement_servie';
        }

        return 'en_attente';
    }
}
