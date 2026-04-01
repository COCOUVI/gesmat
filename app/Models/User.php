<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\CustomResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User - Modèle d'authentification pour les utilisateurs
 *
 * Attributs:
 * - nom: string
 * - prenom: string
 * - email: string (unique)
 * - password: string (hashed)
 * - role: enum(admin, gestionnaire, employe)
 * - service: string
 * - poste: string
 * - email_verified_at: datetime (nullable)
 */
final class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'email',
        'password',
        'prenom',
        'service',
        'role',
        'poste',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relation avec les équipements affectés à cet utilisateur
     */
    public function equipements(): BelongsToMany
    {
        return $this->belongsToMany(Equipement::class, 'affectations')
            ->withPivot('date_retour', 'quantite_affectee', 'created_by')
            ->withTimestamps();
    }

    /**
     * Relation avec les pannes signalées par cet utilisateur
     */
    public function pannes(): HasMany
    {
        return $this->hasMany(Panne::class);
    }

    /**
     * Relation avec les demandes d'équipement de cet utilisateur
     */
    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class);
    }

    /**
     * Relation avec les bons associés à cet utilisateur
     */
    public function bons(): HasMany
    {
        return $this->hasMany(Bon::class);
    }

    /**
     * Relation avec les demandes assignées à cet utilisateur (gestionnaire)
     */
    public function demandesAssignees(): HasMany
    {
        return $this->hasMany(Demande::class, 'gestionnaire_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
