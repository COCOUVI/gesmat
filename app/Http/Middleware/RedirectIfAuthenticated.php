<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $role = Auth::user()->role;

            return match ($role) {
                'admin','gestionnaire' => redirect('/dashboard'),
                'employe', 'employé', 'employée' => redirect('/dashboard/employe'),
            };
        }

        return $next($request);
    }
}
