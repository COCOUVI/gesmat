<?php

declare(strict_types=1);

use App\Models\Bon;
use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('admin can add an equipment with initial state and image', function () {
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
            'image_path' => UploadedFile::fake()->image('onduleur.jpg'),
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('pdf');

    $equipement = Equipement::where('nom', 'Onduleur')->first();

    expect($equipement)->not->toBeNull();
    expect($equipement->quantite)->toBe(3);
    expect($equipement->image_path)->not->toBeNull();
    expect(Bon::where('user_id', $admin->id)->where('statut', 'entrée')->exists())->toBeTrue();
});
