<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\CollaborateurExterne;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Http\UploadedFile;

it('can create an external collaborator with identity card', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $response = $this->post(route('HandleCollaborator'), [
        'nom' => 'Kouassi',
        'prenom' => 'Brice',
        'chemin_carte' => UploadedFile::fake()->image('carte.png'),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $collaborateur = CollaborateurExterne::where('nom', 'Kouassi')
        ->where('prenom', 'Brice')
        ->first();

    expect($collaborateur)->not->toBeNull();
    expect($collaborateur->carte_chemin)->not->toBeNull();
});

it('can create affectation for external collaborator with sortie bon', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $collaborateur = CollaborateurExterne::factory()->create([
        'nom' => 'Dupont',
        'prenom' => 'Jean',
    ]);
    $equipement = Equipement::factory()->create(['quantite' => 100]);

    $response = $this->post('/dashboard/post_bon_collaborator_external', [
        'collaborateur_id' => $collaborateur->id,
        'motif' => 'Equipement pour chantier',
        'type' => 'sortie',
        'equipements' => [$equipement->id],
        'quantites' => [10],
        'dates_retour' => [now()->addDays(10)->toDateString()],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Verify sortie bon created
    $bon = Bon::where('collaborateur_externe_id', $collaborateur->id)
        ->where('statut', 'sortie')
        ->first();

    expect($bon)->not->toBeNull();
    expect($bon->equipements()->count())->toBe(1);

    // Verify affectation created
    $affectation = Affectation::where('collaborateur_externe_id', $collaborateur->id)
        ->where('equipement_id', $equipement->id)
        ->first();

    expect($affectation)->not->toBeNull();
    expect($affectation->quantite_affectee)->toBe(10);
    expect($affectation->statut)->toBe('active');
    expect($affectation->estPourCollaborateur())->toBeTrue();
    expect($affectation->date_retour?->toDateString())->toBe(now()->addDays(10)->toDateString());

    // Verify stock decreased
    $equipement->refresh();
    expect($equipement->getQuantiteDisponible())->toBe(90);
});

it('can return equipment from collaborator via BackTool', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $collaborateur = CollaborateurExterne::factory()->create();
    $equipement = Equipement::factory()->create(['quantite' => 100]);

    // Create affectation
    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'collaborateur_externe_id' => $collaborateur->id,
        'quantite_affectee' => 10,
        'quantite_retournee' => 0,
        'statut' => 'active',
        'created_by' => 'Test User',
    ]);

    // Return equipment via BackTool
    $response = $this->post("/dashboard/back_tool/{$affectation->id}", [
        'quantite_saine_retournee' => 8,
        'pannes_retournees' => [],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Retour du matériel enregistré avec succès');

    // Verify entrée bon created
    $entreeBon = Bon::where('collaborateur_externe_id', $collaborateur->id)
        ->where('statut', 'entrée')
        ->first();

    expect($entreeBon)->not->toBeNull();

    // Verify affectation marked as returned
    $affectation->refresh();
    expect($affectation->quantite_retournee)->toBe(8);
    expect($affectation->statut)->toBe('retour_partiel');
    expect($affectation->returned_at)->not->toBeNull();

    // Verify stock increased
    $equipement->refresh();
    expect($equipement->getQuantiteDisponible())->toBe(98); // 100 - 10 + 8
});

it('complete collaborator workflow creates two bons', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $collaborateur = CollaborateurExterne::factory()->create();
    $equipement = Equipement::factory()->create(['quantite' => 50]);

    // Step 1: Assign equipment (creates sortie bon + affectation)
    $this->post('/dashboard/post_bon_collaborator_external', [
        'collaborateur_id' => $collaborateur->id,
        'motif' => 'Travaux site A',
        'type' => 'sortie',
        'equipements' => [$equipement->id],
        'quantites' => [5],
    ]);

    $sortieBon = Bon::where('collaborateur_externe_id', $collaborateur->id)
        ->where('statut', 'sortie')
        ->first();

    expect($sortieBon)->not->toBeNull();

    // Get affectation
    $affectation = Affectation::where('collaborateur_externe_id', $collaborateur->id)->first();

    // Step 2: Return equipment via BackTool (creates entrée bon)
    $this->post("/dashboard/back_tool/{$affectation->id}", [
        'quantite_saine_retournee' => 5,
        'pannes_retournees' => [],
    ]);

    // Verify both bons exist
    $allBons = Bon::where('collaborateur_externe_id', $collaborateur->id)->get();

    expect($allBons)->toHaveCount(2);
    expect($allBons->where('statut', 'sortie')->first())->not->toBeNull();
    expect($allBons->where('statut', 'entrée')->first())->not->toBeNull();

    // Verify final stock
    $equipement->refresh();
    expect($equipement->getQuantiteDisponible())->toBe(50); // Fully returned
});

it('collaborator affectation uses correct polymorphic fields', function () {
    $collaborateur = CollaborateurExterne::factory()->create([
        'nom' => 'Martin',
        'prenom' => 'Pierre',
    ]);
    $equipement = Equipement::factory()->create();

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'collaborateur_externe_id' => $collaborateur->id,
        'quantite_affectee' => 3,
        'statut' => 'active',
        'created_by' => 'Test User',
    ]);

    expect($affectation->collaborateur_externe_id)->toBe($collaborateur->id);
    expect($affectation->user_id)->toBeNull();
    expect($affectation->estPourCollaborateur())->toBeTrue();
    expect($affectation->estPourEmploye())->toBeFalse();
    expect($affectation->getNomDestinataire())->toContain('Martin');
    expect($affectation->getNomDestinataire())->toContain('Pierre');
});
