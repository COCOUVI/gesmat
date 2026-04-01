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
    public function up()
    {
        if (! Schema::hasColumn('bons', 'fichier_pdf')) {
            Schema::table('bons', function (Blueprint $table) {
                $table->string('fichier_pdf')->nullable()->after('statut');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bons', function (Blueprint $table) {
            $table->dropColumn('fichier_pdf');
        });
    }
};
