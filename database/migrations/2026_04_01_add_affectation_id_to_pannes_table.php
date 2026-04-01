<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la relation affectation_id à la table pannes
     * Permet de tracer qui avait l'équipement quand la panne a été signalée
     */
    public function up(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            $table->foreignId('affectation_id')->nullable()->constrained()->onDelete('cascade')->after('equipement_id')->comment('Affectation où la panne a été signalée');
        });
    }

    /**
     * Supprime la colonne affectation_id
     */
    public function down(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['affectation_id']);
            $table->dropColumn('affectation_id');
        });
    }
};
