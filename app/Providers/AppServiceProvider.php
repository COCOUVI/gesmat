<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
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
        //
        Validator::replacer('auth.failed', function () {
            return 'Adresse e-mail ou mot de passe incorrect.';
        });
        Paginator::useBootstrap();

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
