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
 * - quantite_retournee_stock: integer (quantité revenue au stock alors que la panne n'est pas résolue)
 * - quantite_resolue: integer (quantité réparée/résolue sur cette panne)
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
        'quantite_retournee_stock',
        'quantite_resolue',
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

    /**
     * Quantité de cette panne déjà revenue au stock.
     */
    public function getQuantiteRetourneeAuStock(): int
    {
        if ($this->affectation_id === null) {
            return 0;
        }

        $quantiteRetourneeAuStock = (int) $this->quantite_retournee_stock;

        if ($quantiteRetourneeAuStock === 0 && $this->affectation?->estCompletementRetournee()) {
            return (int) $this->quantite;
        }

        return max(0, $quantiteRetourneeAuStock);
    }

    /**
     * Quantité encore physiquement chez l'employé pour cette panne.
     */
    public function getQuantiteEncoreChezEmploye(): int
    {
        if ($this->statut === 'resolu') {
            return 0;
        }

        if ($this->affectation_id === null) {
            return 0;
        }

        $quantiteInitialeChezEmploye = max(0, $this->quantite - $this->getQuantiteRetourneeAuStock());

        return max(0, $quantiteInitialeChezEmploye - $this->getQuantiteResolue());
    }

    /**
     * Quantité déjà résolue sur cette panne.
     */
    public function getQuantiteResolue(): int
    {
        if ($this->statut === 'resolu') {
            return (int) $this->quantite;
        }

        return max(0, (int) $this->quantite_resolue);
    }

    /**
     * Quantité encore en panne dans le stock interne.
     */
    public function getQuantiteInterneNonResolue(): int
    {
        if ($this->statut === 'resolu') {
            return 0;
        }

        if ($this->affectation_id === null) {
            return max(0, $this->quantite - $this->getQuantiteResolue());
        }

        $quantiteRetournee = $this->getQuantiteRetourneeAuStock();
        $quantiteInitialeChezEmploye = max(0, $this->quantite - $quantiteRetournee);
        $quantiteResolueEnInterne = max(0, $this->getQuantiteResolue() - $quantiteInitialeChezEmploye);

        return max(0, $quantiteRetournee - $quantiteResolueEnInterne);
    }

    /**
     * Quantité totale encore non résolue sur cette panne.
     */
    public function getQuantiteNonResolue(): int
    {
        return $this->getQuantiteEncoreChezEmploye() + $this->getQuantiteInterneNonResolue();
    }

    /**
     * Quantité qu'il est possible de résoudre maintenant.
     * On ne résout que ce qui est déjà revenu au stock interne.
     */
    public function getQuantiteResolvable(): int
    {
        return $this->getQuantiteNonResolue();
    }

    /**
     * Indique si la panne correspond à du stock interne.
     */
    public function estInterne(): bool
    {
        return $this->affectation_id === null;
    }

    /**
     * Libellé métier de l'origine de la panne.
     */
    public function getOrigineLibelle(): string
    {
        return $this->estInterne()
            ? 'Stock interne'
            : 'Affectation #'.$this->affectation_id;
    }
}
