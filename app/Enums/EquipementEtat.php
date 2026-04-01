<?php

declare(strict_types=1);

namespace App\Enums;

enum EquipementEtat: string
{
    case DISPONIBLE = 'disponible';
    case USAGÉ = 'usagé';
    case EN_PANNE = 'en panne';
    case RÉPARÉ = 'réparé';

    /**
     * Get all available states as key-value pairs for select options
     */
    public static function options(): array
    {
        return [
            self::DISPONIBLE->value => 'disponible',
            self::USAGÉ->value => 'usagé',
            self::EN_PANNE->value => 'en panne',
            self::RÉPARÉ->value => 'réparé',
        ];
    }

    /**
     * Get the French label for the status
     */
    public function label(): string
    {
        return self::options()[$this->value] ?? $this->value;
    }

    /**
     * Check if the equipment is available for assignment
     */
    public function isAvailable(): bool
    {
        return $this === self::DISPONIBLE;
    }

    /**
     * Check if the equipment is broken
     */
    public function isBroken(): bool
    {
        return $this === self::EN_PANNE;
    }
}
