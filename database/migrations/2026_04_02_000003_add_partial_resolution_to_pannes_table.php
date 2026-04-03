<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            if (! Schema::hasColumn('pannes', 'quantite_resolue')) {
                $table->integer('quantite_resolue')
                    ->default(0)
                    ->after('quantite_retournee_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            if (Schema::hasColumn('pannes', 'quantite_resolue')) {
                $table->dropColumn('quantite_resolue');
            }
        });
    }
};
