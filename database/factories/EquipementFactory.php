<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Categorie;
use App\Models\Equipement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipement>
 */
final class EquipementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->words(3, true),
            'marque' => fake()->company(),
            'description' => fake()->sentence(),
            'date_acquisition' => fake()->date(),
            'quantite' => fake()->numberBetween(50, 200),
            'seuil_critique' => fake()->numberBetween(5, 20),
            'image_path' => 'images/equipment_'.uniqid().'.jpg',
            'categorie_id' => Categorie::factory(),
        ];
    }
}
