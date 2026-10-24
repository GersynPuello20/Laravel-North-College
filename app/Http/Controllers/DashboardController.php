<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SessionAttendance;
use App\Models\User;
use App\Services\Attendance\DashboardStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isStaff() || $user->isTeacher()) {
            $stats = new DashboardStats($user);

            return view('dashboard.index', [
                'stats' => $stats,
                'today' => $stats->today(),
                'todaySessions' => $stats->todaySessions(),
                'rate' => $stats->rate(),
                'activeStudents' => $stats->activeStudents(),
                'unclosed' => $stats->unclosedSessions(),
                'needsReview' => $stats->needsReview(),
                'coverage' => $stats->biometricCoverage(),
                'facialFailures' => $stats->facialFailures(),
                'activity' => $stats->recentActivity(),
            ]);
        }

        if ($user->isStudent()) {
            return $this->student($user);
        }

        return view('dashboard.pending');
    }

    private function student(User $user): View
    {
        $totals = $user->sessionAttendances()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $all = $totals->sum();
        $attended = ($totals[SessionAttendance::STATUS_PRESENT] ?? 0) + ($totals[SessionAttendance::STATUS_LATE] ?? 0);

        $courseIds = $user->enrollments()->where('status', 'active')->pluck('course_id');

        return view('dashboard.student', [
            'totals' => collect(SessionAttendance::STATUS_LABELS)->map(fn ($label, $status) => (int) ($totals[$status] ?? 0)),
            'rate' => $all > 0 ? round($attended * 100 / $all, 1) : null,
            'recent' => $user->sessionAttendances()->with('classSession.course')->latest('recorded_at')->limit(6)->get(),
            'upcoming' => ClassSession::with('course')
                ->whereIn('course_id', $courseIds)
                ->where('status', '<>', ClassSession::STATUS_CLOSED)
                ->where('ends_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),
            'enrollments' => Enrollment::with('course.professor')->where('student_id', $user->id)->latest()->get(),
            'grades' => Grade::with('subject.course')->where('student_id', $user->id)->latest()->limit(5)->get(),
            'consent' => $user->activeBiometricConsent,
            'hasTemplate' => $user->faceTemplate()->exists(),
        ]);
    }
}
