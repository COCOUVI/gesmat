<?php

namespace App\Enums;

enum EquipementEtat: string
{
    case Disponible = 'disponible';
    case Usage = 'usagé';
    case EnPanne = 'en panne';
    case Repare = 'réparé';
}
