<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute la migration.
     */
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            if (! Schema::hasColumn('equipements', 'quantite')) {
                $table->integer('quantite')->default(0);
            }
        });
    }

    /**
     * Annule la migration.
     */
    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            if (Schema::hasColumn('equipements', 'quantite')) {
                $table->dropColumn('quantite');
            }
        });
    }
};
