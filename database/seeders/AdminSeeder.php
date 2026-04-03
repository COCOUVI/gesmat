<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class AdminSeeder extends Seeder
{
    public function run()
    {
        // Solution recommandée : updateOrCreate
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nom' => 'Administrateur',
                'prenom' => 'Jaspe',
                'password' => bcrypt('admin123@'),
                'role' => 'admin',
                'poste' => 'CEO',
                'email_verified_at' => now(),
                'service' => 'JASPE_DIRECTION',
            ]
        );
    }
}
