<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\User;

test('debug affectation process', function (): void {
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

    // Try with middleware bypassed
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

    echo 'Response Status: '.$response->status()."\n";

    // Check if affectation was created
    $affectation = Affectation::where('user_id', $employee->id)
        ->where('equipement_id', $equipement->id)
        ->first();

    if ($affectation) {
        echo "✓ Affectation created successfully\n";
        echo '  ID: '.$affectation->id."\n";
        echo '  Quantite: '.$affectation->quantite_affectee."\n";
        expect($affectation->quantite_affectee)->toBe(3);
    } else {
        echo "✗ Affectation not found!\n";
        expect(true)->toBeFalse();
    }
});
