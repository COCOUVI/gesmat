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
 * Note: L'état de l'équipement est géré via la table "pannes"
 * - Pas de panne = disponible
 * - Panne(s) non résolue(s) = en panne
 * - Panne(s) résolue(s) = réparé
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
     * Calcule la quantité affectée (assignée et non retournée)
     * Formule: SUM(affectations.quantite_affectee WHERE date_retour IS NULL)
     */
    public function getQuantiteAffectee(): int
    {
        return (int) $this->affectations()
            ->whereNull('date_retour')
            ->sum('quantite_affectee');
    }

    /**
     * Calcule la quantité en panne (non résolue)
     * Formule: SUM(pannes.quantite WHERE statut != 'resolu')
     */
    public function getQuantiteEnPanne(): int
    {
        return (int) $this->pannes()
            ->where('statut', '!=', 'resolu')
            ->sum('quantite');
    }

    /**
     * Calcule la quantité disponible pour affectation
     * Formule: quantite_total - quantite_affectee - quantite_en_panne
     */
    public function getQuantiteDisponible(): int
    {
        $affectee = $this->getQuantiteAffectee();
        $enPanne = $this->getQuantiteEnPanne();

        return max(0, $this->quantite - $affectee - $enPanne);
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

        if ($enPanne === 0) {
            return 'disponible';
        }

        $affectee = $this->getQuantiteAffectee();
        $affecteeEnPanne = $this->affectations()
            ->whereNull('date_retour')
            ->whereHas('pannes', function ($q) {
                $q->where('statut', '!=', 'resolu');
            })
            ->sum('quantite_affectee');

        if ($affecteeEnPanne === $affectee && $affectee > 0) {
            return 'en panne';
        }

        return 'partiellement en panne';
    }
}
