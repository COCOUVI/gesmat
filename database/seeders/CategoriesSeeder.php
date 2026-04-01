<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Matériel Informatique',
            'Matériel Réseau & Télécom',
            'Outils d’Intervention Terrain',
            'Équipements de Sécurité',
            'Appareils de Communication',
            'Logiciels & Licences',
            'Équipements de Mobilité',
            'Mobilier de Bureau',
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'nom' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
