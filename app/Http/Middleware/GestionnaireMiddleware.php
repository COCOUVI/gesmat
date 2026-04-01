<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

final class GestionnaireMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'gestionnaire') {
            return $next($request);
        }

        abort(403, 'Accès refusé.');
    }
}
