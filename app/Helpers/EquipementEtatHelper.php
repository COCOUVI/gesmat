<?php

namespace App\Helpers;

use App\Enums\EquipementEtat;

class EquipementEtatHelper
{
    /**
     * Retourne le label français formaté d'un état d'équipement
     *
     * @param string $etat Valeur de l'état
     * @return string Label formaté
     */
    public static function label(string $etat): string
    {
        return match ($etat) {
            EquipementEtat::DISPONIBLE->value => 'Disponible',
            EquipementEtat::USAGÉ->value => 'Usagé',
            EquipementEtat::EN_PANNE->value => 'En panne',
            EquipementEtat::RÉPARÉ->value => 'Réparé',
            default => $etat,
        };
    }

    /**
     * Retourne la classe CSS pour le badge de statut
     *
     * @param string $etat Valeur de l'état
     * @return string Classe CSS
     */
    public static function badgeClass(string $etat): string
    {
        return match ($etat) {
            EquipementEtat::DISPONIBLE->value => 'status-available',
            EquipementEtat::USAGÉ->value => 'status-used',
            EquipementEtat::EN_PANNE->value => 'status-broken',
            EquipementEtat::RÉPARÉ->value => 'status-repaired',
            default => 'status-unknown',
        };
    }

    /**
     * Retourne la couleur Bootstrap pour le badge de statut
     *
     * @param string $etat Valeur de l'état
     * @return string Classe couleur Bootstrap
     */
    public static function badgeColor(string $etat): string
    {
        return match ($etat) {
            EquipementEtat::DISPONIBLE->value => 'success',
            EquipementEtat::USAGÉ->value => 'warning',
            EquipementEtat::EN_PANNE->value => 'danger',
            EquipementEtat::RÉPARÉ->value => 'info',
            default => 'secondary',
        };
    }

    /**
     * Retourne toutes les options d'état pour les sélecteurs HTML
     *
     * @return array Format: ['value' => 'label']
     */
    public static function options(): array
    {
        $options = [];
        foreach (EquipementEtat::cases() as $etat) {
            $options[$etat->value] = self::label($etat->value);
        }
        return $options;
    }
}
