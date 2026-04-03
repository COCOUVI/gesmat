<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class EquipementDemandé extends Model
{
    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }
}

// public function up()
// {
//     Schema::table('equipement_demandés', function (Blueprint $table) {
//         $table->unsignedBigInteger('demande_id')->nullable();

//         $table->foreign('demande_id')
//               ->references('id')->on('demandes')
//               ->onDelete('cascade');
//     });
