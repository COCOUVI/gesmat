<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Centralizes affectations for both employees and external collaborators.
     * Affectations now support both user_id (employees) and collaborateur_externe_id (collaborators).
     */
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            // Add support for external collaborators
            if (! Schema::hasColumn('affectations', 'collaborateur_externe_id')) {
                $table->foreignId('collaborateur_externe_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('collaborateur_externes')
                    ->cascadeOnDelete();
            }

            // Add actual return timestamp (not just return date)
            if (! Schema::hasColumn('affectations', 'returned_at')) {
                $table->timestamp('returned_at')
                    ->nullable()
                    ->after('date_retour')
                    ->comment('Timestamp of actual return to stock');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            if (Schema::hasColumn('affectations', 'returned_at')) {
                $table->dropColumn('returned_at');
            }

            if (Schema::hasColumn('affectations', 'collaborateur_externe_id')) {
                $table->dropForeignKey(['collaborateur_externe_id']);
                $table->dropColumn('collaborateur_externe_id');
            }
        });
    }
};
