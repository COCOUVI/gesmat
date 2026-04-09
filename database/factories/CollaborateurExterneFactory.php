<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CollaborateurExterne;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollaborateurExterne>
 */
final class CollaborateurExterneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'carte_chemin' => fake()->randomNumber(5),
        ];
    }
}
