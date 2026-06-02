<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        if (in_array(auth()->user()->role?->slug, $roles, true)) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'No tienes acceso a esta sección.');
    }
}
