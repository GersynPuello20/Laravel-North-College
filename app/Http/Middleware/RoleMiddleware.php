<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles = null): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Acceso no autorizado.');
        }

        if ($user->isSuspended() || $user->isPending()) {
            abort(403, 'La cuenta no está activa.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (! $roles) {
            return $next($request);
        }

        $allowed = collect(explode('|', $roles))->contains(fn ($role) => $user->hasRole($role));

        if (! $allowed) {
            abort(403, 'No tiene permisos suficientes para acceder a esta área.');
        }

        return $next($request);
    }
}
