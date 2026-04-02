<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Equipement - Modèle pour la gestion des équipements et du stock
 *
 * Attributs:
 * - nom: string
 * - marque: string
 * - description: longText
 * - date_acquisition: date
 * - quantite: integer (stock total)
 * - image_path: string
 * - categorie_id: foreignId
 *
 * Note: le stock disponible est dérivé des affectations et des pannes.
 * - quantite = stock physique total
 * - les pannes sur affectations actives sont déjà incluses dans la quantité affectée
 * - seules les pannes internes/non affectées ou revenues au stock bloquent à nouveau le disponible
 */
final class Equipement extends Model
{
    protected $fillable = ['nom', 'marque', 'description', 'date_acquisition', 'image_path', 'categorie_id', 'quantite'];

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

    /**
     * Calcule la quantité affectée active (sortie réelle non encore retournée).
     */
    public function getQuantiteAffectee(): int
    {
        $affectations = $this->relationLoaded('affectations')
            ? $this->affectations
            : $this->affectations()->get();

        return (int) $affectations->sum(fn (Affectation $affectation) => $affectation->getQuantiteActive());
    }

    /**
     * Calcule la quantité en panne sur des affectations encore actives.
     * Cette quantité ne doit pas être soustraite une seconde fois du disponible.
     */
    public function getQuantiteEnPanneAffectee(): int
    {
        $pannes = $this->relationLoaded('pannes')
            ? $this->pannes
            : $this->pannes()->with('affectation')->get();

        return (int) $pannes
            ->filter(fn (Panne $panne) => $panne->statut !== 'resolu' && $panne->affectation?->getQuantiteActive() > 0)
            ->sum(fn (Panne $panne) => $panne->getQuantiteEncoreChezEmploye());
    }

    /**
     * Calcule la quantité en panne revenue au stock ou non affectée.
     * Cette quantité bloque réellement le stock disponible.
     */
    public function getQuantiteEnPanneInterne(): int
    {
        $pannes = $this->relationLoaded('pannes')
            ? $this->pannes
            : $this->pannes()->with('affectation')->get();

        return (int) $pannes
            ->filter(fn (Panne $panne) => $panne->statut !== 'resolu')
            ->sum(fn (Panne $panne) => $panne->getQuantiteInterneNonResolue());
    }

    /**
     * Calcule la quantité totale en panne (non résolue), tous contextes confondus.
     */
    public function getQuantiteEnPanne(): int
    {
        return $this->getQuantiteEnPanneAffectee() + $this->getQuantiteEnPanneInterne();
    }

    /**
     * Calcule la quantité disponible pour affectation
     * Formule: quantite_totale - quantite_affectee_active - quantite_en_panne_interne
     */
    public function getQuantiteDisponible(): int
    {
        $affectee = $this->getQuantiteAffectee();
        $enPanneInterne = $this->getQuantiteEnPanneInterne();

        return max(0, $this->quantite - $affectee - $enPanneInterne);
    }

    /**
     * Vérifie si la quantité demandée est disponible
     */
    public function peutAffecter(int $quantiteDemandee): bool
    {
        return $this->getQuantiteDisponible() >= $quantiteDemandee;
    }

    /**
     * Retourne l'état réel de l'équipement basé sur les pannes
     */
    public function getEtat(): string
    {
        $enPanne = $this->getQuantiteEnPanne();
        $disponible = $this->getQuantiteDisponible();

        if ($enPanne === 0) {
            return $disponible > 0 ? 'disponible' : 'non disponible';
        }

        if ($disponible === 0) {
            return 'en panne';
        }

        return 'partiellement en panne';
    }

    /**
     * Scope pour obtenir uniquement les équipements avec du stock disponible
     * Utilisé pour les demandes et assignations
     */
    public function scopeWithStock($query)
    {
        return $query->whereRaw(
            "equipements.quantite
            - COALESCE((
                SELECT SUM(
                    CASE
                        WHEN affectations.statut = ? THEN 0
                        WHEN affectations.quantite_affectee > COALESCE(affectations.quantite_retournee, 0)
                            THEN affectations.quantite_affectee - COALESCE(affectations.quantite_retournee, 0)
                        ELSE 0
                    END
                )
                FROM affectations
                WHERE affectations.equipement_id = equipements.id
            ), 0)
            - COALESCE((
                SELECT SUM(
                    CASE
                        WHEN pannes.affectation_id IS NULL
                            AND pannes.quantite > COALESCE(pannes.quantite_resolue, 0)
                            THEN pannes.quantite - COALESCE(pannes.quantite_resolue, 0)
                        WHEN COALESCE(pannes.quantite_retournee_stock, 0) > COALESCE(pannes.quantite_resolue, 0)
                            THEN COALESCE(pannes.quantite_retournee_stock, 0) - COALESCE(pannes.quantite_resolue, 0)
                        WHEN affectations.statut = ?
                            AND pannes.quantite > COALESCE(pannes.quantite_resolue, 0)
                            THEN pannes.quantite - COALESCE(pannes.quantite_resolue, 0)
                        ELSE 0
                    END
                )
                FROM pannes
                LEFT JOIN affectations ON affectations.id = pannes.affectation_id
                WHERE pannes.equipement_id = equipements.id
                  AND pannes.statut != ?
            ), 0) >= 1",
            ['retourné', 'retourné', 'resolu']
        );
    }
}
