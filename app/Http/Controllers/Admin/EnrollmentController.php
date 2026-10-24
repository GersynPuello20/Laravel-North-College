<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $courseId = $request->integer('course_id') ?: null;

        $enrollments = Enrollment::with(['student', 'course.professor'])
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $courses = Course::orderBy('name')->get(['id', 'name']);
        $students = User::students()->active()
            ->when($courseId, fn ($q) => $q->whereDoesntHave('enrollments', fn ($e) => $e->where('course_id', $courseId)))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('enrollments.index', compact('enrollments', 'courses', 'students', 'courseId'));
    }

    /** Matrícula hecha por el administrador. */
    public function adminStore(Request $request)
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', Rule::exists('users', 'id')],
        ]);

        $students = User::students()->whereIn('id', $data['student_ids'])->pluck('id');
        $created = 0;

        foreach ($students as $studentId) {
            $enrollment = Enrollment::firstOrCreate(
                ['student_id' => $studentId, 'course_id' => $data['course_id']],
                ['enrollment_date' => now()->toDateString(), 'status' => 'active']
            );
            $created += $enrollment->wasRecentlyCreated ? 1 : 0;
        }

        AuditLog::record('enrollment.created', Course::find($data['course_id']), ['students' => $students->all()]);

        return redirect()->route('admin.enrollments.index', ['course_id' => $data['course_id']])
            ->with('success', $created === 1 ? 'Estudiante matriculado.' : "Se matricularon {$created} estudiantes.");
    }

    public function studentIndex()
    {
        $enrollments = Enrollment::with('course.professor')
            ->where('student_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('enrollments.student_index', compact('enrollments'));
    }

    public function create()
    {
        $user = Auth::user();

        $enrolledCourseIds = $user->enrollments()->pluck('course_id')->toArray();

        $courses = Course::with('professor')
            ->where('status', 'active')
            ->whereNotIn('id', $enrolledCourseIds)
            ->orderBy('name')
            ->get();

        return view('enrollments.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => ['required', Rule::exists('courses', 'id')->where('status', 'active')],
        ]);

        $enrollment = Enrollment::firstOrCreate(
            ['student_id' => Auth::id(), 'course_id' => $data['course_id']],
            ['enrollment_date' => now()->toDateString(), 'status' => 'active']
        );

        return redirect()->route('student.enrollments.index')->with(
            $enrollment->wasRecentlyCreated ? 'success' : 'info',
            $enrollment->wasRecentlyCreated ? 'Matrícula completada correctamente.' : 'Ya estabas matriculado en ese curso.'
        );
    }

    public function destroy(Enrollment $enrollment)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();

        if (! $isAdmin && $enrollment->student_id !== $user->id) {
            abort(403);
        }

        $enrollment->delete();

        if ($isAdmin) {
            AuditLog::record('enrollment.deleted', $enrollment->course, ['student_id' => $enrollment->student_id]);
        }

        return back()->with('success', 'Matrícula eliminada correctamente.');
    }
}
