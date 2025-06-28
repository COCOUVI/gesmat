<?php

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
        Schema::table('bons', function (Blueprint $table) {
            if (!Schema::hasColumn('bons', 'fichier_pdf')) {
                $table->string("fichier_pdf");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bons', function (Blueprint $table) {
            //
        });
    }
};
