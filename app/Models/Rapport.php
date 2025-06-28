<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $fillable = [
        'contenu',
        'user_id',
        'file_path'
        // Ajoutez ici tous les champs que vous souhaitez pouvoir assigner en masse
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}