<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class CollaborateurExterne extends Model
{
    use HasFactory;

    protected $table = 'collaborateur_externes';

    protected $fillable = [
        'nom',
        'prenom',
        'carte_chemin',
    ];
}
