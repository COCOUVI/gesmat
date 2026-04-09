<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Affectation;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;
use App\Observers\AffectationObserver;
use App\Observers\DemandeObserver;
use App\Observers\EquipementObserver;
use App\Observers\PanneObserver;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
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
        Equipement::observe($this->app->make(EquipementObserver::class));
        Affectation::observe($this->app->make(AffectationObserver::class));
        Panne::observe($this->app->make(PanneObserver::class));
        Demande::observe($this->app->make(DemandeObserver::class));
        User::observe($this->app->make(UserObserver::class));

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
