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
            if (! Schema::hasColumn('affectations', 'demande_id')) {
                $table->foreignId('demande_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('demandes')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            if (Schema::hasColumn('affectations', 'demande_id')) {
                $table->dropConstrainedForeignId('demande_id');
            }
        });
    }
};
