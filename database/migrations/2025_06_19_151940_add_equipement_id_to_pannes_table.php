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
    // public function up(): void
    // {
    //     Schema::table('pannes', function (Blueprint $table) {
    //         //
    //          $table->unsignedBigInteger('equipement_id')->after('id'); // ou à la fin si tu préfères
    //          $table->foreign('equipement_id')->references('id')->on('equipements')->onDelete('cascade');
    //     });
    // }

    public function up()
    {
        Schema::table('pannes', function (Blueprint $table) {
            if (! Schema::hasColumn('pannes', 'equipement_id')) {
                $table->unsignedBigInteger('equipement_id')->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            //
        });
    }
};
