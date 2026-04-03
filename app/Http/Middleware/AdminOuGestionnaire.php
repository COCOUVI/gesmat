<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class AdminOuGestionnaire
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && in_array($user->role, ['admin', 'gestionnaire'])) {
            return $next($request);
        }

        abort(403, 'Accès refusé');
    }
}
