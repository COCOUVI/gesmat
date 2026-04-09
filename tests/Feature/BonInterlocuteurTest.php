<?php

declare(strict_types=1);

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\CollaborateurExterne;
use App\Models\Equipement;
use App\Models\User;

test('bon tracks interlocuteur type for employee affectation', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employe']);
    $equipement = Equipement::factory()->create(['quantite' => 10]);

    $bon = Bon::create([
        'user_id' => $employee->id,
        'motif' => 'Test affectation',
        'statut' => 'sortie',
        'fichier_pdf' => 'test/bon.pdf',
        'interlocuteur_type' => 'user',
        'interlocuteur_id' => $employee->id,
    ]);

    expect($bon->interlocuteur_type)->toBe('user');
    expect($bon->interlocuteur_id)->toBe($employee->id);
    expect($bon->getInterlocuteurNom())->toContain($employee->nom);
});

test('bon tracks interlocuteur type for external collaborator affectation', function (): void {
    $collaborateur = CollaborateurExterne::factory()->create();
    $equipement = Equipement::factory()->create(['quantite' => 10]);

    $bon = Bon::create([
        'collaborateur_externe_id' => $collaborateur->id,
        'motif' => 'Test affectation collaborateur',
        'statut' => 'sortie',
        'fichier_pdf' => 'test/bon.pdf',
        'interlocuteur_type' => 'collaborateur_externe',
        'interlocuteur_id' => $collaborateur->id,
    ]);

    expect($bon->interlocuteur_type)->toBe('collaborateur_externe');
    expect($bon->interlocuteur_id)->toBe($collaborateur->id);
    expect($bon->getInterlocuteurNom())->toContain($collaborateur->nom);
    expect($bon->getInterlocuteurNom())->toContain($collaborateur->prenom);
});

test('bon tracks libre source for equipment entry', function (): void {
    $bon = Bon::create([
        'motif' => 'Réapprovisionnement',
        'statut' => 'entrée',
        'fichier_pdf' => 'test/bon.pdf',
        'interlocuteur_type' => 'libre',
        'interlocuteur_nom_libre' => 'Fournisseur XYZ',
    ]);

    expect($bon->interlocuteur_type)->toBe('libre');
    expect($bon->interlocuteur_nom_libre)->toBe('Fournisseur XYZ');
    expect($bon->getInterlocuteurNom())->toBe('Fournisseur XYZ');
});

test('bon returns equipment return interlocuteur properly', function (): void {
    $employee = User::factory()->create(['role' => 'employe']);
    $equipement = Equipement::factory()->create(['quantite' => 10]);
    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'quantite_affectee' => 5,
        'statut' => 'active',
        'created_by' => 'Test User',
    ]);

    $bon = Bon::create([
        'user_id' => $employee->id,
        'motif' => 'Retour d\'équipement',
        'statut' => 'entrée',
        'fichier_pdf' => 'test/bon.pdf',
        'interlocuteur_type' => 'user',
        'interlocuteur_id' => $employee->id,
    ]);

    expect($bon->interlocuteur_type)->toBe('user');
    expect($bon->interlocuteur_id)->toBe($employee->id);
    expect($bon->getInterlocuteur()->id)->toBe($employee->id);
});

test('bon returns identity parts for libre and collaborator sources', function (): void {
    $collaborateur = CollaborateurExterne::factory()->create([
        'nom' => 'Tossou',
        'prenom' => 'Alice',
    ]);

    $collaborateurBon = Bon::create([
        'collaborateur_externe_id' => $collaborateur->id,
        'motif' => 'Livraison',
        'statut' => 'entrée',
        'fichier_pdf' => 'test/collab.pdf',
        'interlocuteur_type' => 'collaborateur_externe',
        'interlocuteur_id' => $collaborateur->id,
    ]);

    $libreBon = Bon::create([
        'motif' => 'Réapprovisionnement libre',
        'statut' => 'entrée',
        'fichier_pdf' => 'test/libre.pdf',
        'interlocuteur_type' => 'libre',
        'interlocuteur_nom_libre' => 'Jean Dupont',
    ]);

    expect($collaborateurBon->getInterlocuteurIdentityParts())->toBe([
        'nom' => 'Tossou',
        'prenom' => 'Alice',
    ]);
    expect($libreBon->getInterlocuteurIdentityParts())->toBe([
        'nom' => 'Jean Dupont',
        'prenom' => '',
    ]);
});
