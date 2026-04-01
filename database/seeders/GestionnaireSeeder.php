<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class GestionnaireSeeder extends Seeder
{
    public function run()
    {
        // Solution recommandée : updateOrCreate
        User::updateOrCreate(
            ['email' => 'aden@gmail.com'],
            [
                'nom' => 'Aden',
                'prenom' => 'Gest',
                'password' => bcrypt('aden123@'),
                'role' => 'gestionnaire',
                'service' => 'Informatique',
                'poste' => 'Responsable IT',
                'email_verified_at' => now(),
            ]
        );
    }
}
