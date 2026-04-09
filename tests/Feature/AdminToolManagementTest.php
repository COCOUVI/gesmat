<?php

declare(strict_types=1);

use App\Models\Bon;
use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('admin can add an equipment with initial state and image', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $categorie = Categorie::create(['nom' => 'Ajouts']);

    $response = $this->actingAs($admin)
        ->withoutMiddleware()
        ->post(route('addTool'), [
            'nom' => 'Onduleur',
            'marque' => 'APC',
            'categorie_id' => $categorie->id,
            'description' => 'Onduleur pour serveurs',
            'date_acquisition' => now()->toDateString(),
            'quantite' => 3,
            'seuil_critique' => 1,
            'image_path' => UploadedFile::fake()->image('onduleur.jpg'),
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('pdf');

    $equipement = Equipement::where('nom', 'Onduleur')->first();

    expect($equipement)->not->toBeNull();
    expect($equipement->quantite)->toBe(3);
    expect($equipement->seuil_critique)->toBe(1);
    expect($equipement->image_path)->not->toBeNull();
    expect(Bon::where('user_id', $admin->id)->where('statut', 'entrée')->exists())->toBeTrue();
});

test('admin can update an equipment critical threshold', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $categorie = Categorie::create(['nom' => 'Mises à jour']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Imprimante',
        'marque' => 'Brother',
        'description' => 'Imprimante bureau',
        'date_acquisition' => now(),
        'quantite' => 4,
        'seuil_critique' => 1,
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)
        ->put(route('putTool', $equipement), [
            'nom' => 'Imprimante',
            'marque' => 'Brother',
            'categorie_id' => $categorie->id,
            'description' => 'Imprimante bureau',
            'date_acquisition' => now()->toDateString(),
            'quantite' => 4,
            'seuil_critique' => 2,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($equipement->fresh()->seuil_critique)->toBe(2);
});

test('admin can replenish an equipment and receive an entry bon pdf link', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $categorie = Categorie::create(['nom' => 'Réapprovisionnements']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Routeur',
        'marque' => 'Cisco',
        'description' => 'Routeur agence',
        'date_acquisition' => now(),
        'quantite' => 6,
        'seuil_critique' => 2,
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)->post(route('replenish.equipment'), [
        'equipement_id' => $equipement->id,
        'quantite' => 4,
        'is_anonymous' => 1,
        'deposant_anonymous_nom' => 'Fournisseur',
        'deposant_anonymous_prenom' => 'Jaspe',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('pdf');

    $equipement->refresh();

    expect($equipement->quantite)->toBe(10);

    $bon = Bon::query()->latest()->first();

    expect($bon)->not->toBeNull();
    expect($bon->statut)->toBe('entrée');
    expect($bon->interlocuteur_type)->toBe('libre');
    expect($bon->interlocuteur_nom_libre)->toBe('Fournisseur Jaspe');
});
