<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
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
            EquipementSeeder::class
        ]);
    }
}
