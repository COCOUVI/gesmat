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
        Schema::table('affectations', function (Blueprint $table) {
            if (! Schema::hasColumn('affectations', 'quantite_retournee')) {
                $table->integer('quantite_retournee')
                    ->default(0)
                    ->after('quantite_affectee');
            }
        });

        Schema::table('pannes', function (Blueprint $table) {
            if (! Schema::hasColumn('pannes', 'quantite_retournee_stock')) {
                $table->integer('quantite_retournee_stock')
                    ->default(0)
                    ->after('quantite');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            if (Schema::hasColumn('pannes', 'quantite_retournee_stock')) {
                $table->dropColumn('quantite_retournee_stock');
            }
        });

        Schema::table('affectations', function (Blueprint $table) {
            if (Schema::hasColumn('affectations', 'quantite_retournee')) {
                $table->dropColumn('quantite_retournee');
            }
        });
    }
};
