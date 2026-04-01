<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\User;

test('admin can performaffectation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

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

    // Verify equipment is available for affectation
    expect($equipement->peutAffecter(3))->toBeTrue();

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(
            route('handle.affectation'),
            [
                'employe_id' => $employee->id,
                'motif' => 'Test affectation',
                'equipements' => [$equipement->id],
                'quantites' => [3],
                'dates_retour' => [null],
            ]
        );

    // Check response - should redirect if successful
    expect($response->status())->not->toBe(500);

    // Check if affectation was created
    $affectation = Affectation::where('user_id', $employee->id)
        ->where('equipement_id', $equipement->id)
        ->first();

    expect($affectation)->not->toBeNull();
    expect($affectation->quantite_affectee)->toBe(3);
});
