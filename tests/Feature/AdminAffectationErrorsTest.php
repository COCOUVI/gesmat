<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;

test('admin affectation fails with insufficient stock', function (): void {
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

test('admin affectation fails when employee is missing', function (): void {
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

test('admin affectation fails when cumulative duplicated lines exceed available stock', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Stock cumule']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Imprimante laser',
        'marque' => 'HP',
        'description' => 'Imprimante du bureau',
        'quantite' => 4,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(route('handle.affectation'), [
            'employe_id' => $employee->id,
            'motif' => 'Affectation sur deux lignes',
            'equipements' => [$equipement->id, $equipement->id],
            'quantites' => [2, 3],
            'dates_retour' => [null, null],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(Affectation::where('user_id', $employee->id)->count())->toBe(0);
});

test('admin affectation fails when target user is not an employee', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $gestionnaire = User::factory()->create(['role' => 'gestionnaire']);

    $categorie = Categorie::create(['nom' => 'Role']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Projecteur',
        'marque' => 'Epson',
        'description' => 'Projecteur de reunion',
        'quantite' => 3,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(route('handle.affectation'), [
            'employe_id' => $gestionnaire->id,
            'motif' => 'Test role invalide',
            'equipements' => [$equipement->id],
            'quantites' => [1],
            'dates_retour' => [null],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(Affectation::where('user_id', $gestionnaire->id)->count())->toBe(0);
});

test('admin cannot cancel affectation after partial return', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Retour']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Station',
        'marque' => 'Dell',
        'description' => 'Station fixe',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'quantite_affectee' => 3,
        'quantite_retournee' => 1,
        'created_by' => 'Admin Test',
        'statut' => 'retour_partiel',
    ]);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->delete(route('affectation.annuler', $affectation));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(Affectation::find($affectation->id))->not->toBeNull();
});

test('admin cannot cancel affectation with panne history', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Panne']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Imprimante',
        'marque' => 'Brother',
        'description' => 'Imprimante test',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'quantite_affectee' => 2,
        'created_by' => 'Admin Test',
        'statut' => 'active',
    ]);

    Panne::create([
        'equipement_id' => $equipement->id,
        'affectation_id' => $affectation->id,
        'user_id' => $employee->id,
        'quantite' => 1,
        'description' => 'Historique panne',
        'statut' => 'en_attente',
    ]);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->delete(route('affectation.annuler', $affectation));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(Affectation::find($affectation->id))->not->toBeNull();
});
