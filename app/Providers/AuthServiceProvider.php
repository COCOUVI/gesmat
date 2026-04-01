<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Equipement;
use App\Policies\EquipementPolicy;
// Ajoute ces lignes :
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Equipement::class => EquipementPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Tu peux ajouter ici d'autres règles Gate si besoin
    }
}
