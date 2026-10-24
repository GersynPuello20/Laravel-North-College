<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SessionAttendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyAttendanceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $records = $user->sessionAttendances()
            ->with(['classSession.course', 'corrections'])
            ->latest('recorded_at')
            ->paginate(20);

        $totals = $user->sessionAttendances()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('student.attendance', [
            'records' => $records,
            'totals' => collect(SessionAttendance::STATUS_LABELS)->map(fn ($label, $status) => (int) ($totals[$status] ?? 0)),
        ]);
    }
}
