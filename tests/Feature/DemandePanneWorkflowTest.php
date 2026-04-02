<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\Categorie;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;

test('demande validation is blocked when computed available stock is insufficient', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Demandes']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Imprimante',
        'marque' => 'HP',
        'description' => 'Imprimante reseau',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => null,
        'quantite_affectee' => 4,
        'created_by' => 'Admin Test',
    ]);

    $demande = Demande::create([
        'lieu' => 'Bureau',
        'motif' => 'Besoin pour impression',
        'statut' => 'en_attente',
        'user_id' => $employee->id,
    ]);

    $demande->equipements()->attach($equipement->id, ['nbr_equipement' => 2]);

    $response = $this->actingAs($admin)->put(route('valider.demande', $demande), [
        'quantites_a_affecter' => [$equipement->id => 2],
        'dates_retour' => [now()->addWeek()->toDateString()],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(Affectation::where('demande_id', $demande->id)->count())->toBe(0);
    expect($demande->fresh()->statut)->toBe('en_attente');
});

test('demande validation does not double count pannes on active affectations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Demandes Active Panne']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Imprimante reseau',
        'marque' => 'Brother',
        'description' => 'Imprimante reseau test',
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
        'description' => 'Une unite en panne dans une affectation active',
        'statut' => 'en_attente',
    ]);

    $demande = Demande::create([
        'lieu' => 'Atelier',
        'motif' => 'Besoin standard',
        'statut' => 'en_attente',
        'user_id' => $employee->id,
    ]);

    $demande->equipements()->attach($equipement->id, ['nbr_equipement' => 7]);

    $response = $this->actingAs($admin)->put(route('valider.demande', $demande), [
        'quantites_a_affecter' => [$equipement->id => 7],
        'dates_retour' => [now()->addWeek()->toDateString()],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $affectationDemande = Affectation::where('demande_id', $demande->id)->first();

    expect($affectationDemande)->not->toBeNull();
    expect($affectationDemande->quantite_affectee)->toBe(7);
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(0);
});

test('demande validation is blocked by unresolved pannes returned to internal stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Demandes Internal Panne']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Scanner casse',
        'marque' => 'Xerox',
        'description' => 'Scanner avec panne interne',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectationRetournee = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => now(),
        'quantite_affectee' => 3,
        'created_by' => 'Admin Test',
        'statut' => 'retourné',
    ]);

    Panne::create([
        'equipement_id' => $equipement->id,
        'affectation_id' => $affectationRetournee->id,
        'user_id' => $employee->id,
        'quantite' => 1,
        'description' => 'Une unite retournee en panne non resolue',
        'statut' => 'en_attente',
    ]);

    $demande = Demande::create([
        'lieu' => 'Siege',
        'motif' => 'Demande bloquee par panne interne',
        'statut' => 'en_attente',
        'user_id' => $employee->id,
    ]);

    $demande->equipements()->attach($equipement->id, ['nbr_equipement' => 10]);

    $response = $this->actingAs($admin)->put(route('valider.demande', $demande), [
        'quantites_a_affecter' => [$equipement->id => 10],
        'dates_retour' => [now()->addWeek()->toDateString()],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(Affectation::where('demande_id', $demande->id)->count())->toBe(0);
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(9);
});

test('demande validation creates linked affectation with return date and generated bon', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Demandes OK']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Scanner',
        'marque' => 'Canon',
        'description' => 'Scanner de bureau',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $demande = Demande::create([
        'lieu' => 'Direction',
        'motif' => 'Besoin temporaire',
        'statut' => 'en_attente',
        'user_id' => $employee->id,
    ]);

    $returnDate = now()->addDays(10)->toDateString();

    $demande->equipements()->attach($equipement->id, ['nbr_equipement' => 2]);

    $response = $this->actingAs($admin)->put(route('valider.demande', $demande), [
        'quantites_a_affecter' => [$equipement->id => 2],
        'dates_retour' => [$equipement->id => $returnDate],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('pdf');

    $affectation = Affectation::where('demande_id', $demande->id)->first();

    expect($affectation)->not->toBeNull();
    expect($affectation->user_id)->toBe($employee->id);
    expect($affectation->equipement_id)->toBe($equipement->id);
    expect($affectation->quantite_affectee)->toBe(2);
    expect($affectation->date_retour?->toDateString())->toBe($returnDate);
    expect($demande->fresh()->statut)->toBe('acceptee');
    expect($equipement->fresh()->quantite)->toBe(5);
    expect(Bon::where('user_id', $employee->id)->where('statut', 'sortie')->exists())->toBeTrue();
});

test('demande can be partially served then completed later', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Demandes Partielles']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Ordinateur portable',
        'marque' => 'Dell',
        'description' => 'Portable test',
        'quantite' => 8,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $demande = Demande::create([
        'lieu' => 'Service finance',
        'motif' => 'Dotation progressive',
        'statut' => 'en_attente',
        'user_id' => $employee->id,
    ]);

    $demande->equipements()->attach($equipement->id, ['nbr_equipement' => 5]);

    $firstResponse = $this->actingAs($admin)->put(route('valider.demande', $demande), [
        'quantites_a_affecter' => [$equipement->id => 3],
        'dates_retour' => [$equipement->id => now()->addDays(7)->toDateString()],
    ]);

    $firstResponse->assertRedirect();
    $firstResponse->assertSessionHas('success');

    $demande->refresh()->load(['equipements', 'affectations']);

    expect($demande->statut)->toBe('en_attente');
    expect($demande->estPartiellementServie())->toBeTrue();
    expect($demande->getQuantiteTotaleServie())->toBe(3);
    expect($demande->getQuantiteRestantePourEquipement($equipement->id, 5))->toBe(2);

    $secondResponse = $this->actingAs($admin)->put(route('valider.demande', $demande), [
        'quantites_a_affecter' => [$equipement->id => 2],
        'dates_retour' => [$equipement->id => now()->addDays(14)->toDateString()],
    ]);

    $secondResponse->assertRedirect();
    $secondResponse->assertSessionHas('success');

    $demande->refresh()->load(['equipements', 'affectations']);

    expect($demande->statut)->toBe('acceptee');
    expect($demande->estEntierementServie())->toBeTrue();
    expect($demande->getQuantiteTotaleServie())->toBe(5);
});

test('employee can report breakdowns by affectation and quantity remaining', function () {
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Pannes']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Imprimante',
        'marque' => 'Epson',
        'description' => 'Imprimante multifonction',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation1 = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => null,
        'quantite_affectee' => 2,
        'created_by' => 'Admin Test',
    ]);

    $affectation2 = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => null,
        'quantite_affectee' => 1,
        'created_by' => 'Admin Test',
    ]);

    $firstResponse = $this->actingAs($employee)->post(route('post.HandlePanne'), [
        'affectation_id' => $affectation1->id,
        'quantite' => 1,
        'description' => 'Premiere panne sur la premiere affectation',
    ]);

    $firstResponse->assertRedirect();
    $firstResponse->assertSessionHas('success');

    $secondResponse = $this->actingAs($employee)->post(route('post.HandlePanne'), [
        'affectation_id' => $affectation2->id,
        'quantite' => 1,
        'description' => 'Panne sur la deuxieme affectation',
    ]);

    $secondResponse->assertRedirect();
    $secondResponse->assertSessionHas('success');

    $thirdResponse = $this->actingAs($employee)->from(route('signaler.panne'))->post(route('post.HandlePanne'), [
        'affectation_id' => $affectation1->id,
        'quantite' => 2,
        'description' => 'Cette quantite depasse le restant de la premiere affectation',
    ]);

    $thirdResponse->assertRedirect(route('signaler.panne'));
    $thirdResponse->assertSessionHasErrors('quantite');

    expect(Panne::where('affectation_id', $affectation1->id)->sum('quantite'))->toBe(1);
    expect(Panne::where('affectation_id', $affectation2->id)->sum('quantite'))->toBe(1);
});

test('partial healthy return increases available stock and keeps affectation active', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Retours sains']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Projecteur',
        'marque' => 'Sony',
        'description' => 'Projecteur HD',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => now()->addDay(),
        'quantite_affectee' => 3,
        'created_by' => 'Admin Test',
    ]);

    expect($equipement->fresh()->getQuantiteDisponible())->toBe(2);

    $response = $this->actingAs($admin)->post(route('affectation.retourner', $affectation), [
        'quantite_saine_retournee' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $affectation->refresh();
    $equipement->refresh();

    expect($affectation->quantite_retournee)->toBe(1);
    expect($affectation->getQuantiteActive())->toBe(2);
    expect($affectation->statut)->toBe('retour_partiel');
    expect($equipement->getQuantiteDisponible())->toBe(3);
});

test('returning unresolved broken quantity keeps available stock unchanged and moves panne to internal stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Retours panne']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Imprimante laser',
        'marque' => 'HP',
        'description' => 'Imprimante laser reseau',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => now()->addDay(),
        'quantite_affectee' => 3,
        'created_by' => 'Admin Test',
    ]);

    $panne = Panne::create([
        'equipement_id' => $equipement->id,
        'affectation_id' => $affectation->id,
        'user_id' => $employee->id,
        'quantite' => 1,
        'description' => 'Une unite en panne chez lemploye',
        'statut' => 'en_attente',
    ]);

    expect($equipement->fresh()->getQuantiteDisponible())->toBe(7);

    $response = $this->actingAs($admin)->post(route('affectation.retourner', $affectation), [
        'quantite_saine_retournee' => 0,
        'pannes_retournees' => [
            $panne->id => 1,
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $affectation->refresh();
    $panne->refresh();
    $equipement->refresh();

    expect($affectation->quantite_retournee)->toBe(1);
    expect($affectation->getQuantiteActive())->toBe(2);
    expect($panne->quantite_retournee_stock)->toBe(1);
    expect($equipement->getQuantiteEnPanneAffectee())->toBe(0);
    expect($equipement->getQuantiteEnPanneInterne())->toBe(1);
    expect($equipement->getQuantiteDisponible())->toBe(7);
});

test('admin can declare an internal breakdown from available stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $categorie = Categorie::create(['nom' => 'Pannes internes']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Routeur',
        'marque' => 'Cisco',
        'description' => 'Routeur coeur',
        'quantite' => 6,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    expect($equipement->fresh()->getQuantiteDisponible())->toBe(6);

    $response = $this->actingAs($admin)->post(route('pannes.store-interne'), [
        'equipement_id' => $equipement->id,
        'quantite' => 2,
        'description' => 'Deux unites detectees en panne au magasin',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $panne = Panne::where('equipement_id', $equipement->id)->latest()->first();

    expect($panne)->not->toBeNull();
    expect($panne->affectation_id)->toBeNull();
    expect($panne->getQuantiteInterneNonResolue())->toBe(2);
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(4);
});

test('admin can partially resolve an internal breakdown and restore available stock progressively', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $categorie = Categorie::create(['nom' => 'Resolution partielle']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Switch',
        'marque' => 'Netgear',
        'description' => 'Switch reseau',
        'quantite' => 8,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $panne = Panne::create([
        'equipement_id' => $equipement->id,
        'affectation_id' => null,
        'user_id' => $admin->id,
        'quantite' => 3,
        'quantite_retournee_stock' => 0,
        'quantite_resolue' => 0,
        'description' => 'Trois unites en panne interne',
        'statut' => 'en_attente',
    ]);

    expect($equipement->fresh()->getQuantiteDisponible())->toBe(5);

    $response = $this->actingAs($admin)->put(route('pannes.resolu', $panne), [
        'quantite_resolue' => 2,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $panne->refresh();
    $equipement->refresh();

    expect($panne->quantite_resolue)->toBe(2);
    expect($panne->statut)->toBe('en_attente');
    expect($panne->getQuantiteInterneNonResolue())->toBe(1);
    expect($equipement->getQuantiteDisponible())->toBe(7);
});

test('admin can replace a broken assigned unit and create a new replacement affectation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);

    $categorie = Categorie::create(['nom' => 'Remplacement']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Telephone IP',
        'marque' => 'Yealink',
        'description' => 'Telephone bureau',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => now()->addDays(20),
        'quantite_affectee' => 2,
        'created_by' => 'Admin Test',
        'statut' => 'active',
    ]);

    $panne = Panne::create([
        'equipement_id' => $equipement->id,
        'affectation_id' => $affectation->id,
        'user_id' => $employee->id,
        'quantite' => 1,
        'quantite_retournee_stock' => 0,
        'quantite_resolue' => 0,
        'description' => 'Une unite en panne chez lemploye',
        'statut' => 'en_attente',
    ]);

    expect($equipement->fresh()->getQuantiteDisponible())->toBe(3);

    $response = $this->actingAs($admin)->post(route('pannes.remplacer', $panne), [
        'quantite_remplacement' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('pdf');

    $panne->refresh();
    $affectation->refresh();
    $equipement->refresh();

    $affectationsActives = Affectation::where('user_id', $employee->id)
        ->where('equipement_id', $equipement->id)
        ->orderBy('id')
        ->get();

    expect($panne->quantite_retournee_stock)->toBe(1);
    expect($panne->getQuantiteEncoreChezEmploye())->toBe(0);
    expect($panne->getQuantiteInterneNonResolue())->toBe(1);
    expect($affectation->quantite_retournee)->toBe(1);
    expect($affectation->statut)->toBe('retour_partiel');
    expect($affectationsActives)->toHaveCount(2);
    expect($affectationsActives->last()->demande_id)->toBeNull();
    expect($affectationsActives->last()->quantite_affectee)->toBe(1);
    expect($equipement->getQuantiteDisponible())->toBe(2);
    expect(Bon::where('user_id', $employee->id)->where('statut', 'sortie')->count())->toBeGreaterThan(0);
});
