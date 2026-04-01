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
        Schema::table('pannes', function (Blueprint $table) {
            $table->string('statut')->default('en_attente');
        });
    }

    public function down()
    {
        Schema::table('pannes', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};
