<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            AdminSeeder::class,
            GestionnaireSeeder::class,
            CategoriesSeeder::class,
            EquipementSeeder::class,
            EquipementSeeder::class,
        ]);
    }
}
