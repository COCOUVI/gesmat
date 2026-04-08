<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin sees the shared profile page and can update personal information', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
        'nom' => 'Akakpo',
        'prenom' => 'Nadia',
        'poste' => 'Administratrice',
        'service' => 'DSI',
    ]);

    $response = $this->actingAs($admin)->get(route('profile.edit'));

    $response->assertOk();
    $response->assertSee('Mettre à jour mes informations');
    $response->assertSee('Modifier mon mot de passe');

    $updateResponse = $this->actingAs($admin)->patch(route('profile.update'), [
        'nom' => 'Akakpo',
        'prenom' => 'Nadine',
        'email' => 'nadine@example.com',
        'poste' => 'Administratrice système',
        'service' => 'Informatique',
    ]);

    $updateResponse->assertRedirect(route('profile.edit'));
    $updateResponse->assertSessionHas('success');

    $admin->refresh();

    expect($admin->prenom)->toBe('Nadine');
    expect($admin->email)->toBe('nadine@example.com');
    expect($admin->poste)->toBe('Administratrice système');
    expect($admin->service)->toBe('Informatique');
});

test('employee sees the dedicated profile page', function (): void {
    $employee = User::factory()->create([
        'role' => 'employe',
        'poste' => 'Agent support',
    ]);

    $response = $this->actingAs($employee)->get(route('profile.edit'));

    $response->assertOk();
    $response->assertSee('Mon profil');
    $response->assertSee('Informations personnelles');
    $response->assertSee('Sécurité');
});

test('authenticated user can update password from profile page', function (): void {
    $user = User::factory()->create([
        'role' => 'gestionnaire',
        'password' => Hash::make('secret-old-password'),
        'poste' => 'Gestionnaire',
    ]);

    $response = $this->actingAs($user)->put(route('profile.password.update'), [
        'current_password' => 'secret-old-password',
        'password' => 'secret-new-password',
        'password_confirmation' => 'secret-new-password',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('success_password');

    expect(Hash::check('secret-new-password', $user->fresh()->password))->toBeTrue();
});
