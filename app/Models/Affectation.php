<?php

// app/Models/Affectation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
// <<<<<<< HEAD
     protected $fillable = [
         'equipement_id', 'user_id', 'date_retour','created_by'
     ];
    protected $casts = [
        'date_retour' => 'datetime',


    ];

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
