<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Acceso no autorizado.');
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isTeacher()) {
            return $this->teacherDashboard();
        }

        if ($user->isStudent()) {
            return $this->studentDashboard();
        }

        abort(403, 'Rol no válido.');
    }

    public function adminDashboard()
    {
        $stats = [
            'students' => User::whereRole('student')->count(),
            'teachers' => User::whereRole('teacher')->count(),
            'courses' => Course::count(),
            'enrollments' => Enrollment::count(),
            'subjects' => Subject::count(),
            'grades' => Grade::count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    public function teacherDashboard()
    {
        $service = app(\App\Services\AttendancePeriodService::class);

        $courses = Course::where('professor_id', Auth::id())->get();
        $students = Enrollment::whereHas('course', fn ($query) => $query->where('professor_id', Auth::id()))
            ->distinct('student_id')
            ->count('student_id');
        $gradeCount = Grade::whereHas('subject.course', fn ($query) => $query->where('professor_id', Auth::id()))->count();
        $attendanceStats = $service->getLast30DaysStats(Auth::id());
        $current = $service->getCurrentQuincena();
        $lastClosed = $service->getLastClosedQuincena();

        return view('dashboard.teacher', compact('courses', 'students', 'gradeCount', 'attendanceStats', 'current', 'lastClosed'));
    }

    public function studentDashboard()
    {
        $enrollments = Enrollment::with('course')
            ->where('student_id', Auth::id())
            ->latest()
            ->limit(6)
            ->get();
        $grades = Grade::with('subject.course')
            ->where('student_id', Auth::id())
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.student', compact('enrollments', 'grades'));
    }
}
