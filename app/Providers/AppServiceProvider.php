<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\DemandeServed;
use App\Events\DemandeSubmitted;
use App\Events\DirectAffectationCreated;
use App\Events\EquipementStockChanged;
use App\Events\EquipmentReturned;
use App\Events\PanneReplacementCompleted;
use App\Events\PanneReported;
use App\Events\PanneResolved;
use App\Listeners\SendCriticalStockAlert;
use App\Listeners\SendDemandeServedNotifications;
use App\Listeners\SendDemandeSubmittedNotifications;
use App\Listeners\SendDirectAffectationNotifications;
use App\Listeners\SendEquipmentReturnedNotifications;
use App\Listeners\SendPanneReplacementNotifications;
use App\Listeners\SendPanneReportedNotifications;
use App\Listeners\SendPanneResolvedNotifications;
use App\Models\Affectation;
use App\Models\Equipement;
use App\Models\Panne;
use App\Observers\AffectationObserver;
use App\Observers\EquipementObserver;
use App\Observers\PanneObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::replacer('auth.failed', function () {
            return 'Adresse e-mail ou mot de passe incorrect.';
        });
        Paginator::useBootstrap();
        Event::listen(DemandeSubmitted::class, SendDemandeSubmittedNotifications::class);
        Event::listen(DemandeServed::class, SendDemandeServedNotifications::class);
        Event::listen(DirectAffectationCreated::class, SendDirectAffectationNotifications::class);
        Event::listen(EquipmentReturned::class, SendEquipmentReturnedNotifications::class);
        Event::listen(PanneReported::class, SendPanneReportedNotifications::class);
        Event::listen(PanneResolved::class, SendPanneResolvedNotifications::class);
        Event::listen(PanneReplacementCompleted::class, SendPanneReplacementNotifications::class);
        Event::listen(EquipementStockChanged::class, SendCriticalStockAlert::class);
        Equipement::observe($this->app->make(EquipementObserver::class));
        Affectation::observe($this->app->make(AffectationObserver::class));
        Panne::observe($this->app->make(PanneObserver::class));

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
