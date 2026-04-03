<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            if (! Schema::hasColumn('equipements', 'seuil_critique')) {
                $table->integer('seuil_critique')
                    ->default(1)
                    ->after('quantite');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            if (Schema::hasColumn('equipements', 'seuil_critique')) {
                $table->dropColumn('seuil_critique');
            }
        });
    }
};
