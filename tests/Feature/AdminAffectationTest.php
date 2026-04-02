<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\Categorie;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
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

test('admin affectation creates active direct affectation with return date and bon', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Directe']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Ordinateur fixe',
        'marque' => 'Lenovo',
        'description' => 'Poste de travail',
        'quantite' => 6,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $returnDate = now()->addDays(15)->toDateString();

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(route('handle.affectation'), [
            'employe_id' => $employee->id,
            'motif' => 'Dotation initiale',
            'equipements' => [$equipement->id],
            'quantites' => [2],
            'dates_retour' => [$returnDate],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('pdf');

    $affectation = Affectation::where('user_id', $employee->id)
        ->where('equipement_id', $equipement->id)
        ->latest()
        ->first();

    expect($affectation)->not->toBeNull();
    expect($affectation->demande_id)->toBeNull();
    expect($affectation->statut)->toBe('active');
    expect($affectation->date_retour?->toDateString())->toBe($returnDate);
    expect($affectation->getOrigineLibelle())->toBe('Affectation directe');
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(4);
    expect(Bon::where('user_id', $employee->id)->where('statut', 'sortie')->exists())->toBeTrue();
});

test('admin affectation merges duplicated lines with same return date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Fusion']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Ecran',
        'marque' => 'Samsung',
        'description' => 'Ecran bureautique',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $returnDate = now()->addDays(30)->toDateString();

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(route('handle.affectation'), [
            'employe_id' => $employee->id,
            'motif' => 'Dotation fusionnee',
            'equipements' => [$equipement->id, $equipement->id],
            'quantites' => [2, 3],
            'dates_retour' => [$returnDate, $returnDate],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $affectations = Affectation::where('user_id', $employee->id)
        ->where('equipement_id', $equipement->id)
        ->get();

    expect($affectations)->toHaveCount(1);
    expect($affectations->first()->quantite_affectee)->toBe(5);
    expect($affectations->first()->date_retour?->toDateString())->toBe($returnDate);
});

test('admin affectation keeps separate lines when return dates differ', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Dates distinctes']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Clavier',
        'marque' => 'Logitech',
        'description' => 'Clavier test',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(route('handle.affectation'), [
            'employe_id' => $employee->id,
            'motif' => 'Dates distinctes',
            'equipements' => [$equipement->id, $equipement->id],
            'quantites' => [1, 2],
            'dates_retour' => [now()->addDays(10)->toDateString(), now()->addDays(20)->toDateString()],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $affectations = Affectation::where('user_id', $employee->id)
        ->where('equipement_id', $equipement->id)
        ->orderBy('date_retour')
        ->get();

    expect($affectations)->toHaveCount(2);
    expect($affectations->pluck('quantite_affectee')->all())->toBe([1, 2]);
});

test('admin can cancel an affectation without returns or pannes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Annulation']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Souris',
        'marque' => 'HP',
        'description' => 'Souris filaire',
        'quantite' => 4,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => now()->addDays(5),
        'quantite_affectee' => 2,
        'created_by' => 'Admin Test',
        'statut' => 'active',
    ]);

    expect($equipement->fresh()->getQuantiteDisponible())->toBe(2);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->delete(route('affectation.annuler', $affectation));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Affectation::find($affectation->id))->toBeNull();
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(4);
});

test('cancelling a demande affectation recalculates demande status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Demande cancel']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Tablette',
        'marque' => 'Apple',
        'description' => 'Tablette pro',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $demande = Demande::create([
        'lieu' => 'Direction',
        'motif' => 'Besoin tablette',
        'statut' => 'acceptee',
        'user_id' => $employee->id,
    ]);

    $demande->equipements()->attach($equipement->id, ['nbr_equipement' => 2]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'demande_id' => $demande->id,
        'date_retour' => null,
        'quantite_affectee' => 2,
        'created_by' => 'Admin Test',
        'statut' => 'active',
    ]);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->delete(route('affectation.annuler', $affectation));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($demande->fresh()->statut)->toBe('en_attente');
});
