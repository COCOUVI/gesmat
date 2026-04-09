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
        Schema::table('bons', function (Blueprint $table) {
            $table->enum('interlocuteur_type', ['user', 'collaborateur_externe', 'libre'])->nullable()->after('statut');
            $table->unsignedBigInteger('interlocuteur_id')->nullable()->after('interlocuteur_type');
            $table->string('interlocuteur_nom_libre')->nullable()->after('interlocuteur_id');

            $table->index(['interlocuteur_type', 'interlocuteur_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bons', function (Blueprint $table) {
            $table->dropIndex(['interlocuteur_type', 'interlocuteur_id']);
            $table->dropColumn(['interlocuteur_type', 'interlocuteur_id', 'interlocuteur_nom_libre']);
        });
    }
};
