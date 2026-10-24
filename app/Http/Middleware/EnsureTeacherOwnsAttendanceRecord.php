<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTeacherOwnsAttendanceRecord
{
    public function handle(Request $request, Closure $next)
    {
        $attendance = $request->route('attendance');

        if (! $attendance || $attendance->teacher_id !== Auth::id()) {
            abort(403, 'No tiene permisos para acceder a este registro.');
        }

        return $next($request);
    }
}
