<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supprime la colonne etat de la table equipements
     * La gestion de l'état est maintenant centralisée dans la table pannes
     */
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->dropColumn('etat');
        });
    }

    /**
     * Restaure la colonne etat
     */
    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->enum('etat', ['disponible', 'usagé', 'en panne', 'réparé'])
                ->default('disponible')
                ->after('nom');
        });
    }
};
