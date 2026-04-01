<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bon extends Model
{
    use HasFactory;

        protected $fillable = ['user_id', 'motif', 'statut','fichier_pdf'];

        public function user()
        {
            return $this->belongsTo(CollaborateurExterne::class, 'user_id');
        }
}
