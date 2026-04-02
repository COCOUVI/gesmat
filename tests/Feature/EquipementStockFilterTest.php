<?php

declare(strict_types=1);

use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\Affectation;
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

test('employee demand page uses computed available stock and hides fully reserved equipment', function () {
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Category Stock Reel']);

    $visibleEquipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Visible Equipment',
        'marque' => 'Brand A',
        'description' => 'Test description',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $hiddenEquipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Hidden Equipment',
        'marque' => 'Brand B',
        'description' => 'Test description',
        'quantite' => 4,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    Affectation::create([
        'equipement_id' => $visibleEquipement->id,
        'user_id' => $employee->id,
        'date_retour' => null,
        'quantite_affectee' => 3,
        'created_by' => 'Admin Test',
    ]);

    Panne::create([
        'equipement_id' => $visibleEquipement->id,
        'user_id' => $employee->id,
        'quantite' => 1,
        'description' => 'Panne visible equipment',
        'statut' => 'en_attente',
    ]);

    Affectation::create([
        'equipement_id' => $hiddenEquipement->id,
        'user_id' => $employee->id,
        'date_retour' => null,
        'quantite_affectee' => 4,
        'created_by' => 'Admin Test',
    ]);

    Panne::create([
        'equipement_id' => $hiddenEquipement->id,
        'user_id' => $employee->id,
        'quantite' => 1,
        'description' => 'Panne hidden equipment',
        'statut' => 'en_attente',
    ]);

    $response = $this->actingAs($employee)->get(route('demande.equipement'));

    $response->assertOk();
    $response->assertSee('Visible Equipment');
    $response->assertDontSee('Hidden Equipment');
});

test('computed available stock does not double count pannes on active affectations', function () {
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Category No Double Count']);

    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Shared Printer',
        'marque' => 'Brand C',
        'description' => 'Test description',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => null,
        'quantite_affectee' => 3,
        'created_by' => 'Admin Test',
    ]);

    Panne::create([
        'equipement_id' => $equipement->id,
        'affectation_id' => $affectation->id,
        'user_id' => $employee->id,
        'quantite' => 1,
        'description' => 'Panne sur equipement affecte',
        'statut' => 'en_attente',
    ]);

    expect($equipement->fresh()->getQuantiteAffectee())->toBe(3);
    expect($equipement->fresh()->getQuantiteEnPanneAffectee())->toBe(1);
    expect($equipement->fresh()->getQuantiteEnPanneInterne())->toBe(0);
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(7);
});

test('computed available stock excludes unresolved pannes returned to internal stock', function () {
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Category Internal Panne']);

    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Internal Broken Printer',
        'marque' => 'Brand D',
        'description' => 'Test description',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => null,
        'quantite_affectee' => 3,
        'created_by' => 'Admin Test',
        'statut' => 'retourné',
    ]);

    Panne::create([
        'equipement_id' => $equipement->id,
        'affectation_id' => $affectation->id,
        'user_id' => $employee->id,
        'quantite' => 1,
        'description' => 'Panne retournee au stock',
        'statut' => 'en_attente',
    ]);

    expect($equipement->fresh()->getQuantiteAffectee())->toBe(0);
    expect($equipement->fresh()->getQuantiteEnPanneAffectee())->toBe(0);
    expect($equipement->fresh()->getQuantiteEnPanneInterne())->toBe(1);
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(9);
});
