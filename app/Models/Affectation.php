<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Affectation - Modèle pour la gestion des affectations d'équipements
 *
 * Attributs:
 * - equipement_id: foreignId
 * - user_id: foreignId
 * - demande_id: foreignId nullable
 * - date_retour: datetime (nullable)
 * - quantite_affectee: integer
 * - quantite_retournee: integer
 * - created_by: string (nom du créateur)
 */
final class Affectation extends Model
{
    protected $fillable = [
        'equipement_id',
        'user_id',
        'collaborateur_externe_id',
        'demande_id',
        'date_retour',
        'returned_at',
        'quantite_affectee',
        'quantite_retournee',
        'created_by',
        'motif',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_retour' => 'datetime',
            'returned_at' => 'datetime',
            'equipement_id' => 'integer',
            'user_id' => 'integer',
            'collaborateur_externe_id' => 'integer',
            'demande_id' => 'integer',
            'quantite_affectee' => 'integer',
            'quantite_retournee' => 'integer',
        ];
    }

    /**
     * Relation avec l'équipement affecté
     */
    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }

    /**
     * Relation avec l'utilisateur ayant l'affectation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le collaborateur externe ayant l'affectation (polymorphe)
     */
    public function collaborateurExterne(): BelongsTo
    {
        return $this->belongsTo(CollaborateurExterne::class);
    }

    /**
     * Demande d'origine, si l'affectation découle d'une demande.
     */
    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    /**
     * Relation avec les pannes liées à cette affectation
     */
    public function pannes(): HasMany
    {
        return $this->hasMany(Panne::class);
    }

    /**
     * Scope des affectations toujours en circulation.
     */
    public function scopeActive($query)
    {
        return $query->whereRaw('quantite_affectee > COALESCE(quantite_retournee, 0)');
    }

    /**
     * Vérifie si cette affectation a des pannes non résolues
     */
    public function aPannesNonResolues(): bool
    {
        return $this->pannes()
            ->where('statut', '!=', 'resolu')
            ->exists();
    }

    /**
     * Obtient le nombre de pannes non résolues
     */
    public function getQuantitePannesNonResolues(): int
    {
        return (int) $this->pannes()
            ->where('statut', '!=', 'resolu')
            ->get()
            ->sum(fn (Panne $panne) => $panne->getQuantiteEncoreChezEmploye());
    }

    /**
     * Calcule la quantité restante encore disponible pour signalement de panne.
     */
    public function getQuantiteDisponiblePourPanne(): int
    {
        return max(0, $this->getQuantiteActive() - $this->getQuantitePannesNonResolues());
    }

    /**
     * Quantité totale déjà revenue au stock pour cette affectation.
     */
    public function getQuantiteRetournee(): int
    {
        $quantiteRetournee = (int) $this->quantite_retournee;

        if ($quantiteRetournee === 0 && $this->statut === 'retourné') {
            return (int) $this->quantite_affectee;
        }

        return max(0, $quantiteRetournee);
    }

    /**
     * Quantité encore physiquement chez l'employé.
     */
    public function getQuantiteActive(): int
    {
        return max(0, $this->quantite_affectee - $this->getQuantiteRetournee());
    }

    /**
     * Quantité saine encore détenue par l'employé.
     */
    public function getQuantiteSaineActive(): int
    {
        return max(0, $this->getQuantiteActive() - $this->getQuantitePannesNonResolues());
    }

    /**
     * Libellé métier de l'origine de l'affectation.
     */
    public function estPourCollaborateur(): bool
    {
        return $this->collaborateur_externe_id !== null;
    }

    public function estPourEmploye(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Récupère le destinataire (User ou CollaborateurExterne) de l'affectation.
     */
    public function getDestinataire(): User|CollaborateurExterne|null
    {
        return $this->estPourCollaborateur()
            ? $this->collaborateurExterne
            : $this->user;
    }

    /**
     * Récupère le nom complet du destinataire de l'affectation.
     */
    public function getNomDestinataire(): string
    {
        if ($this->estPourCollaborateur()) {
            $collab = $this->collaborateurExterne;

            return mb_trim(($collab->prenom ?? '').' '.($collab->nom ?? ''));
        }

        return $this->user?->name ?? 'Inconnu';
    }

    /**
     * Récupère l'email du destinataire (null pour collaborateurs externes).
     */
    public function getEmailDestinataire(): ?string
    {
        return $this->estPourEmploye() ? $this->user?->email : null;
    }

    /**
     * Libellé métier de l'origine de l'affectation.
     */
    public function getOrigineLibelle(): string
    {
        if ($this->estPourCollaborateur()) {
            return 'Affectation collaborateur';
        }

        return $this->demande_id !== null
            ? 'Demande acceptee'
            : 'Affectation directe';
    }

    /**
     * Statut d'affichage métier pour éviter de dépendre d'un champ parfois legacy.
     */
    public function getStatutAffichage(): string
    {
        if ($this->estCompletementRetournee()) {
            return 'retourné';
        }

        if ($this->getQuantiteRetournee() > 0) {
            return 'retour_partiel';
        }

        return $this->statut ?: 'active';
    }

    /**
     * Nombre total de pannes liées à cette affectation, y compris l'historique.
     */
    public function getNombrePannes(): int
    {
        if (array_key_exists('pannes_count', $this->attributes)) {
            return (int) $this->attributes['pannes_count'];
        }

        return $this->pannes()->count();
    }

    /**
     * Indique si l'affectation peut être annulée sans perdre d'historique métier.
     */
    public function peutEtreAnnulee(): bool
    {
        return $this->getQuantiteRetournee() === 0 && $this->getNombrePannes() === 0;
    }

    /**
     * Explique pourquoi l'annulation est bloquée.
     */
    public function getMotifBlocageAnnulation(): ?string
    {
        if ($this->getQuantiteRetournee() > 0) {
            return 'Une partie de cette affectation a déjà été retournée.';
        }

        if ($this->getNombrePannes() > 0) {
            return 'Cette affectation possède déjà un historique de pannes.';
        }

        return null;
    }

    /**
     * Indique si toute l'affectation est revenue au stock.
     */
    public function estCompletementRetournee(): bool
    {
        return $this->getQuantiteActive() === 0;
    }
}
