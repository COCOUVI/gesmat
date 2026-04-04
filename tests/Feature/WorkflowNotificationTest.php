<?php

declare(strict_types=1);

use App\Mail\HelpRequestMail;
use App\Mail\IdentifiantsEnvoyes;
use App\Mail\WorkflowActionMail;
use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('employee equipment request sends confirmation to employee and notifications to admins and managers', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $manager = User::factory()->create([
        'role' => 'gestionnaire',
        'email' => 'manager@example.com',
    ]);
    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Demandes mail']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Ordinateur',
        'marque' => 'Dell',
        'description' => 'Ordinateur portable',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($employee)->post(route('demande.soumise'), [
        'lieu' => 'Direction',
        'motif' => 'Besoin de travail',
        'equipements' => [$equipement->id],
        'quantites' => [1],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, 3);
});

test('employee breakdown report sends confirmation to employee and notifications to admins and managers', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-panne@example.com',
    ]);
    $manager = User::factory()->create([
        'role' => 'gestionnaire',
        'email' => 'manager-panne@example.com',
    ]);
    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-panne@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Pannes mail']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Imprimante',
        'marque' => 'HP',
        'description' => 'Imprimante reseau',
        'quantite' => 4,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => now()->addDays(4),
        'quantite_affectee' => 2,
        'created_by' => 'Admin Test',
        'statut' => 'active',
    ]);

    $response = $this->actingAs($employee)->post(route('post.HandlePanne'), [
        'affectation_id' => $affectation->id,
        'quantite' => 1,
        'description' => 'Une unite presente une panne importante',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, 3);
    Mail::assertQueued(WorkflowActionMail::class, fn (WorkflowActionMail $mail): bool => $mail->envelope()->subject === 'Votre signalement de panne a bien été enregistré');
    Mail::assertQueued(WorkflowActionMail::class, fn (WorkflowActionMail $mail): bool => $mail->envelope()->subject === 'Nouveau signalement de panne à traiter');
});

test('serving a demande sends the output slip by email only to the employee', function () {
    Storage::fake('public');
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-serve@example.com',
    ]);
    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-serve@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Demande servie mail']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Scanner',
        'marque' => 'Canon',
        'description' => 'Scanner portable',
        'quantite' => 3,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $demande = Demande::create([
        'lieu' => 'Service achats',
        'motif' => 'Besoin de numérisation',
        'statut' => 'en_attente',
        'user_id' => $employee->id,
    ]);
    $demande->equipements()->attach($equipement->id, ['nbr_equipement' => 1]);

    $response = $this->actingAs($admin)->put(route('valider.demande', $demande), [
        'quantites_a_affecter' => [$equipement->id => 1],
        'dates_retour' => [$equipement->id => now()->addWeek()->toDateString()],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, 1);
});

test('direct affectation sends the output slip by email only to the employee', function () {
    Storage::fake('public');
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-affect@example.com',
    ]);
    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-affect@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Affectation mail']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Tablette',
        'marque' => 'Samsung',
        'description' => 'Tablette de terrain',
        'quantite' => 5,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)->post(route('handle.affectation'), [
        'employe_id' => $employee->id,
        'motif' => 'Dotation initiale',
        'equipements' => [$equipement->id],
        'quantites' => [2],
        'dates_retour' => [now()->addDays(15)->toDateString()],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, 1);
});

test('equipment return sends the entry slip by email only to the employee', function () {
    Storage::fake('public');
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-return@example.com',
    ]);
    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-return@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Retour mail']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Projecteur',
        'marque' => 'Sony',
        'description' => 'Projecteur mobile',
        'quantite' => 4,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => now()->addDay(),
        'quantite_affectee' => 2,
        'created_by' => 'Admin Test',
        'statut' => 'active',
    ]);

    $response = $this->actingAs($admin)->post(route('affectation.retourner', $affectation), [
        'quantite_saine_retournee' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, 1);
});

test('breakdown resolution notifies the employee linked to the affectation', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-resolve@example.com',
    ]);
    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-resolve@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Resolution mail']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Telephone',
        'marque' => 'Yealink',
        'description' => 'Telephone IP',
        'quantite' => 2,
        'seuil_critique' => 0,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $affectation = Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => null,
        'quantite_affectee' => 1,
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
        'description' => 'Telephone inutilisable',
        'statut' => 'en_attente',
    ]);

    $response = $this->actingAs($admin)->put(route('pannes.resolu', $panne), [
        'quantite_resolue' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, 1);
    Mail::assertQueued(WorkflowActionMail::class, fn (WorkflowActionMail $mail): bool => $mail->envelope()->subject === 'Votre signalement de panne a été mis à jour');
});

test('breakdown replacement sends the replacement slip only to the employee', function () {
    Storage::fake('public');
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-replace@example.com',
    ]);
    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-replace@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Remplacement mail']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Ecran',
        'marque' => 'LG',
        'description' => 'Ecran 24 pouces',
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
        'description' => 'Un ecran est defectueux',
        'statut' => 'en_attente',
    ]);

    $response = $this->actingAs($admin)->post(route('pannes.remplacer', $panne), [
        'quantite_remplacement' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, 1);
    Mail::assertQueued(WorkflowActionMail::class, fn (WorkflowActionMail $mail): bool => $mail->envelope()->subject === 'Un remplacement de matériel a été effectué'
        && count($mail->attachments()) === 1);
});

test('upcoming return reminder command notifies the employee only once per day', function () {
    Mail::fake();
    Artisan::call('cache:clear');

    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-reminder@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Rappel retour mail']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Webcam',
        'marque' => 'Logitech',
        'description' => 'Webcam HD',
        'quantite' => 2,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    Affectation::create([
        'equipement_id' => $equipement->id,
        'user_id' => $employee->id,
        'date_retour' => now()->addDays(2),
        'quantite_affectee' => 1,
        'quantite_retournee' => 0,
        'created_by' => 'Admin Test',
        'statut' => 'active',
    ]);

    $this->artisan('app:send-upcoming-return-reminders')
        ->expectsOutput('1 rappel(s) de retour envoyé(s).')
        ->assertExitCode(0);

    $this->artisan('app:send-upcoming-return-reminders')
        ->expectsOutput('0 rappel(s) de retour envoyé(s).')
        ->assertExitCode(0);

    Mail::assertQueued(WorkflowActionMail::class, 1);
    Mail::assertQueued(WorkflowActionMail::class, fn (WorkflowActionMail $mail): bool => $mail->envelope()->subject === 'Rappel : une date de retour approche');
});

test('critical stock alert is sent to admins and managers when a direct affectation reaches the threshold', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-critical@example.com',
    ]);
    $manager = User::factory()->create([
        'role' => 'gestionnaire',
        'email' => 'manager-critical@example.com',
    ]);
    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-critical@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Stock critique affectation']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Routeur',
        'marque' => 'Cisco',
        'description' => 'Routeur agence',
        'quantite' => 3,
        'seuil_critique' => 1,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)->post(route('handle.affectation'), [
        'employe_id' => $employee->id,
        'motif' => 'Dotation terrain',
        'equipements' => [$equipement->id],
        'quantites' => [2],
        'dates_retour' => [now()->addDays(10)->toDateString()],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, fn (WorkflowActionMail $mail): bool => $mail->envelope()->subject === 'Alerte stock critique : Routeur');
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(1);
});

test('critical stock alert is sent to admins and managers when an internal breakdown reaches the threshold', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-critical-panne@example.com',
    ]);
    $manager = User::factory()->create([
        'role' => 'gestionnaire',
        'email' => 'manager-critical-panne@example.com',
    ]);

    $categorie = Categorie::create(['nom' => 'Stock critique panne']);
    $equipement = Equipement::create([
        'categorie_id' => $categorie->id,
        'nom' => 'Serveur',
        'marque' => 'HP',
        'description' => 'Serveur principal',
        'quantite' => 4,
        'seuil_critique' => 1,
        'date_acquisition' => now(),
        'image_path' => 'test.jpg',
    ]);

    $response = $this->actingAs($admin)->post(route('pannes.store-interne'), [
        'equipement_id' => $equipement->id,
        'quantite' => 3,
        'description' => 'Trois unités détectées en panne au magasin central',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(WorkflowActionMail::class, fn (WorkflowActionMail $mail): bool => $mail->envelope()->subject === 'Alerte stock critique : Serveur');
    expect($equipement->fresh()->getQuantiteDisponible())->toBe(1);
});

test('employee help request is queued to the configured administrator address', function () {
    Mail::fake();
    config()->set('mail.from.address', 'support@example.com');

    $employee = User::factory()->create([
        'role' => 'employe',
        'email' => 'employee-help@example.com',
    ]);

    $response = $this->actingAs($employee)->post(route('send.aide'), [
        'message' => "J'ai besoin d'assistance sur une affectation active.",
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(HelpRequestMail::class, 1);
    Mail::assertQueued(HelpRequestMail::class, fn (HelpRequestMail $mail): bool => $mail->envelope()->subject === "Demande d'aide d'un employé");
});

test('admin user creation queues the credentials email', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin-register@example.com',
    ]);

    $response = $this->actingAs($admin)->post(route('registerPost'), [
        'nom' => 'Doe',
        'prenom' => 'Jane',
        'email' => 'new-user@example.com',
        'role' => 'employe',
        'service' => 'Informatique',
        'poste' => 'Technicienne',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(IdentifiantsEnvoyes::class, 1);
    Mail::assertQueued(IdentifiantsEnvoyes::class);
});
