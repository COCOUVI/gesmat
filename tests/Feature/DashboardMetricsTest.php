<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('admin dashboard returns aggregated metrics from optimized queries', function (): void {
    Cache::flush();

    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);
    $categorie = Categorie::create(['nom' => 'Dashboard']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Serveur',
        'marque' => 'HP',
        'description' => 'Serveur rack',
        'quantite' => 10,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
        'seuil_critique' => 1,
    ]);

    Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'quantite_affectee' => 5,
        'quantite_retournee' => 2,
        'created_by' => 'Admin Test',
        'statut' => 'retour_partiel',
    ]);

    Panne::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'quantite' => 3,
        'quantite_resolue' => 1,
        'description' => 'Panne dashboard',
        'statut' => 'en_attente',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.homedash'));

    $response->assertOk();
    $response->assertViewHas('nbr_equipement', 10);
    $response->assertViewHas('nbr_user', 2);
    $response->assertViewHas('nbr_affect', 3);
    $response->assertViewHas('nbr_panne', 2);
});

test('employee dashboard returns cached aggregated personal metrics', function (): void {
    Cache::flush();

    $employee = User::factory()->create(['role' => 'employe']);
    $categorie = Categorie::create(['nom' => 'Employee Dashboard']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Laptop',
        'marque' => 'Dell',
        'description' => 'Portable',
        'quantite' => 8,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
        'seuil_critique' => 1,
    ]);

    Demande::create([
        'lieu' => 'Bureau',
        'motif' => 'Besoin 1',
        'statut' => 'acceptee',
        'user_id' => $employee->id,
    ]);

    Demande::create([
        'lieu' => 'Bureau',
        'motif' => 'Besoin 2',
        'statut' => 'en_attente',
        'user_id' => $employee->id,
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'quantite_affectee' => 4,
        'quantite_retournee' => 1,
        'created_by' => 'Admin Test',
        'statut' => 'retour_partiel',
    ]);

    Panne::create([
        'equipement_id' => $equipement->id,
        'affectation_id' => $affectation->id,
        'user_id' => $employee->id,
        'quantite' => 2,
        'quantite_resolue' => 1,
        'description' => 'Panne employee dashboard',
        'statut' => 'en_attente',
    ]);

    $response = $this->actingAs($employee)->get(route('dashboard.employee'));

    $response->assertOk();
    $response->assertViewHas('nbr_accept', 1);
    $response->assertViewHas('nbr_en_attente', 1);
    $response->assertViewHas('nbr_assign', 3);
    $response->assertViewHas('nbr_non_resolue', 1);
});

test('admin layout uses an internal scroll shell for content', function (): void {
    Cache::flush();

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.homedash'));

    $response->assertOk();
    $response->assertSee('class="admin-shell"', false);
    $response->assertSee('data-admin-scroll-shell="true"', false);
    $response->assertSee('admin-shell-content', false);
    $response->assertSee('fixedHeader: false', false);
});
