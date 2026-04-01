<?php

declare(strict_types=1);

use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\User;

test('employee demand page shows only equipements with stock', function () {
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Category Test']);

    // Create equipment with stock
    Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Equipment With Stock',
        'marque' => 'Brand A',
        'description' => 'Test description',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    // Create equipment without stock
    Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Equipment Without Stock',
        'marque' => 'Brand B',
        'description' => 'Test description',
        'quantite' => 0,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($employee)->get('/employee/demande-equipement');

    // Should show equipment with stock
    $response->assertSee('Equipment With Stock');

    // Should NOT show equipment without stock
    $response->assertDontSee('Equipment Without Stock');
});

test('admin affectation page shows only equipements with stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $categorie = Categorie::create(['nom' => 'Category Test']);

    // Create equipment with stock
    Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Equipment With Stock',
        'marque' => 'Brand A',
        'description' => 'Test description',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    // Create equipment without stock
    Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Equipment Without Stock',
        'marque' => 'Brand B',
        'description' => 'Test description',
        'quantite' => 0,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)->get('/dashboard/affectation');

    // Should show equipment with stock
    $response->assertSee('Equipment With Stock');

    // Should NOT show equipment without stock
    $response->assertDontSee('Equipment Without Stock');
});

test('equipement stock scope filters correctly', function () {
    $categorie = Categorie::create(['nom' => 'Test']);

    Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'With Stock',
        'marque' => 'Brand',
        'description' => 'Test',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'No Stock',
        'marque' => 'Brand',
        'description' => 'Test',
        'quantite' => 0,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $equipmentsWithStock = Equipement::withStock()->get();

    expect($equipmentsWithStock->count())->toBe(1);
    expect($equipmentsWithStock->first()->nom)->toBe('With Stock');
});
