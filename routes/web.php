<?php

declare(strict_types=1);
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RapportController;
use App\Http\Middleware\AdminOuGestionnaire;
use App\Http\Middleware\GestionnaireMiddleware;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\Isemp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::prefix('gestionnaire')->middleware(['auth', GestionnaireMiddleware::class])->group(function (): void {

    Route::get('/rapports/create', [RapportController::class, 'create'])
        ->name('gestionnaire.rapports.create')
        ->middleware([GestionnaireMiddleware::class]);

    Route::post('/rapports', [RapportController::class, 'store'])
        ->name('gestionnaire.rapports.store')
        ->middleware([GestionnaireMiddleware::class]);

    Route::get('/rapports', [RapportController::class, 'index'])
        ->name('gestionnaire.rapports.index')
        ->middleware([GestionnaireMiddleware::class]);

    Route::get('/rapports/{id}/download', [RapportController::class, 'download'])
        ->name('gestionnaire.rapports.download')
        ->middleware([GestionnaireMiddleware::class]);

    Route::delete('/rapports/{rapport}', [RapportController::class, 'destroy'])->name('gestionnaire.rapports.destroy');

    Route::get('/gestionnaire/rapports/{id}', [RapportController::class, 'show'])->name('gestionnaire.rapports.show');

});

Route::get('/', function () {
    // Cache la vue complète pendant 60 minutes
    return Cache::remember('welcome_page', 60 * 60, function () {
        return view('welcome')->render();  // on génère la vue puis on la met en cache
    });
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', [AdminController::class, 'ShowHomePage'])->name('admin.homedash')->middleware([AdminOuGestionnaire::class]);
    Route::get('/dashboard/employe', [EmployeController::class, 'index'])->name('dashboard.employee')->middleware([Isemp::class]);
});

Route::get('/redirect-by-role', function () {
    $role = Auth::user()->role;

    return match ($role) {
        'admin','gestionnaire' => redirect('/dashboard'),
        'employe', 'employé', 'employée' => redirect('/dashboard/employe'),

    };
})->middleware(['auth'])->name('verifylogin');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin ou Gestionaire
Route::prefix('dashboard')->middleware(['auth', AdminOuGestionnaire::class])->group(function (): void {
    Route::get('/add_tool', [AdminController::class, 'addToolpage'])
        ->name('addToolpage');
    Route::post('/add-tool_post', [AdminController::class, 'addTool'])
        ->name('addTool');
    Route::get('/list_equip', [AdminController::class, 'ShowToolpage'])
        ->name('ShowToolpage');
    Route::get('/put_tool_page/{equipement}', [AdminController::class, 'putToolpage'])
        ->name('putToolpage');
    Route::put('/put_tool/{equipement}', [AdminController::class, 'putTool'])
        ->name('putTool');
    Route::get('/delete_tool/{equipement}', [AdminController::class, 'DeleteTool'])
        ->name('DeleteTool');
    Route::get('/demandes_list', [AdminController::class, 'ShowAllAsk'])
        ->name('liste.demandes');
    Route::put('/check_demande/{demande}', [AdminController::class, 'CheckAsk'])
        ->name('valider.demande');
    Route::put('/cancel_demande/{demande}', [AdminController::class, 'CancelAsk'])
        ->name('refuser.demande');
    Route::put('/loading_demandes,{demande}', [AdminController::class, 'LoadingAsk'])
        ->name('loading.demande');
    Route::get('/affectation', [AdminController::class, 'Showaffectation'])
        ->name('page.affectation');
    Route::post('/afectation-post', [AdminController::class, 'HandleAffectation'])
        ->name('handle.affectation');
    Route::post('/replenish-equipment', [AdminController::class, 'ReplenishEquipment'])
        ->name('replenish.equipment');
    Route::get('/listes_affectations', [AdminController::class, 'Showlistaffectation'])
        ->name('page.listeAffectations');
    Route::get('/equipement-pannes', [AdminController::class, 'Showpannes'])
        ->name('equipements.pannes');
    Route::post('/equipement-pannes', [AdminController::class, 'StoreInternalPanne'])
        ->name('pannes.store-interne');
    Route::put('/pannes_modify/{panne}', [AdminController::class, 'PutPanne'])
        ->name('pannes.resolu');
    Route::post('/pannes_replace/{panne}', [AdminController::class, 'ReplacePanne'])
        ->name('pannes.remplacer');
    Route::get('/list_tools_lost', [AdminController::class, 'ShowToollost'])
        ->name('tools.lost');
    Route::get('/add-collaborateur-page', [AdminController::class, 'CollaboratorsPage'])
        ->name('CollaboratorsPage');
    Route::post('/collaborator_submit', [AdminController::class, 'HandleCollaborator'])
        ->name('HandleCollaborator');
    Route::get('/list_collaborator', [AdminController::class, 'ShowListCollaborator'])
        ->name('ShowListCollaborator');
    Route::delete('/delete_collaborator/{CollaborateurExterne}', [AdminController::class, 'destroy'])
        ->name('collaborateurs.destroy');
    Route::get('/list_bon', [AdminController::class, 'ShowBons'])
        ->name('liste.bons');
    Route::get('/bons/{bon}/download', [AdminController::class, 'downloadBon'])
        ->name('bons.download');
    Route::get('/bon_collaborator_external', [AdminController::class, 'CreateBon'])
        ->name('CreateBon');
    Route::post('/post_bon_collaborator_external', [AdminController::class, 'HandleBon'])
        ->name('HandleBon');
    Route::post('/back_tool/{affectation}', [AdminController::class, 'BackTool'])
        ->name('affectation.retourner');
    Route::delete('/affectations/{affectation}', [AdminController::class, 'CancelAffectation'])
        ->name('affectation.annuler');

});
Route::prefix('dashboard')->middleware(['auth', IsAdmin::class])->group(function (): void {
    Route::get('/list_users', [AdminController::class, 'showusers'])
        ->name('showusers');
    Route::get('/edituser/{user}', [AdminController::class, 'edituserpage'])
        ->name('edituser')
        ->middleware(['auth', IsAdmin::class]);
    Route::get('deleteuser/{user}', [AdminController::class, 'deleteuser'])
        ->name('deleteuser')
        ->middleware(['auth', IsAdmin::class]);
    Route::put('editusers/{user}', [AdminController::class, 'ModifyUser'])
        ->name('putuser');
    Route::get('/list_rapport', [AdminController::class, 'ShowRapport'])
        ->name('rapport.lists');
});

Route::prefix('employee')->middleware(['auth', Isemp::class])->group(function (): void {

    Route::get('/demande-equipement', [EmployeController::class, 'ShowAskpage'])->name('demande.equipement');
    Route::post('/demande-equipement-soumise', [EmployeController::class, 'SubmitAsk'])->name('demande.soumise');
    Route::get('/signaler-panne', [EmployeController::class, 'signalerPanne'])->name('signaler.panne');
    Route::post('/post-signaler-panne', [EmployeController::class, 'HandlePanne'])->name('post.HandlePanne');
    // //liaison implicite
    // Route::delete('/delete_panne/{panne}',[EmployeController::class,"DeletePanne"])->name("delete.panne");
    // liaison implicite
    Route::get('/equipements-assignes', [EmployeController::class, 'equipementsAssignes'])->name('equipements.assignes');
    Route::get('/help-employee', [EmployeController::class, 'Helppage'])
        ->name('page.aide');
    Route::post('/post-aide', [EmployeController::class, 'HandleHelp'])
        ->name('send.aide');
    Route::delete('/delete_ask/{demande}', [EmployeController::class, 'DeleteAsk'])->name('delete.ask');
    Route::get('/panne_listes', [EmployeController::class, 'ShowPannes'])->name('historique.pannes');
    Route::get('/demandes_list', [EmployeController::class, 'ShowDemandes'])->name('listes.demandes');

});

Route::get('/test-mail', function () {
    try {
        Mail::raw('Ceci est un test simple laracon.', function ($message): void {
            $message->to('nolanpatinde0@gmail.com')
                ->subject('Test depuis Laravel avec Hostinger');
        });

        return 'Email envoyé !';
    } catch (Exception $exception) {
        Log::error('Erreur envoi mail : '.$exception->getMessage());

        return 'Erreur : '.$exception->getMessage();
    }
});

// Queue trigger endpoint (for Hostinger cron with PHP 8.4 web)
Route::get('/trigger-queue', function () {
    // Sécurité : vérifier le token
    $token = request('token');
    if ($token !== env('QUEUE_TRIGGER_TOKEN')) {
        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
    }

    // Ne pas exécuter en local
    if (app()->environment('local')) {
        return response()->json(['status' => 'error', 'message' => 'Not allowed in local environment'], 403);
    }

    try {
        // Exécuter les jobs en attente
        \Illuminate\Support\Facades\Artisan::call('queue:work', [
            '--once' => true,
            '--max-time' => 55, // 55 secondes max (Hostinger limit ~60s)
            '--max-jobs' => 10, // Max 10 jobs par appel
        ]);

        \Illuminate\Support\Facades\Log::info('Queue triggered successfully via webhook');

        return response()->json([
            'status' => 'success',
            'message' => 'Queue processed',
            'timestamp' => now(),
        ]);
    } catch (Exception $exception) {
        \Illuminate\Support\Facades\Log::error('Queue trigger error: ' . $exception->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => $exception->getMessage(),
        ], 500);
    }
})->name('trigger.queue');

// deleteuser
require __DIR__.'/auth.php';
