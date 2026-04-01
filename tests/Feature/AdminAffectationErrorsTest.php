<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\User;

test('admin affectation fails with insufficient stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Test']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Test Equipment',
        'marque' => 'Brand',
        'description' => 'Test',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(
            route('handle.affectation'),
            [
                'employe_id' => $employee->id,
                'motif' => 'Test affectation',
                'equipements' => [$equipement->id],
                'quantites' => [10],
                'dates_retour' => [null],
            ]
        );

    // Should redirect back with error
    $response->assertRedirect();

    // No affectation should be created
    $affectation = Affectation::where('user_id', $employee->id)
        ->where('equipement_id', $equipement->id)
        ->first();

    expect($affectation)->toBeNull();
});

test('admin affectation fails when employee is missing', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $categorie = Categorie::create(['nom' => 'Test']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Test Equipment',
        'marque' => 'Brand',
        'description' => 'Test',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(
            route('handle.affectation'),
            [
                'employe_id' => 99999, // Non-existent employee
                'motif' => 'Test affectation',
                'equipements' => [$equipement->id],
                'quantites' => [3],
                'dates_retour' => [null],
            ]
        );

    // Should redirect back with error
    $response->assertRedirect();
});
