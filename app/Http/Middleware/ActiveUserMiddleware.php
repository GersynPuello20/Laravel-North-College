<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActiveUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Acceso no autorizado.');
        }

        if (! $user->isActive()) {
            abort(403, 'La cuenta no se encuentra activa.');
        }

        return $next($request);
    }
}
