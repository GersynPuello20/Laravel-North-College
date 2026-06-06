<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = Enrollment::with(['student.role', 'course.professor'])->latest()->paginate(20);

        return view('enrollments.index', compact('enrollments'));
    }

    public function studentIndex()
    {
        $enrollments = Enrollment::with('course')
            ->where('student_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('enrollments.student_index', compact('enrollments'));
    }

    public function create()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Acceso no autorizado.');
        }

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
            'course_id' => 'required|exists:courses,id',
        ]);

        Enrollment::create([
            'student_id' => Auth::id(),
            'course_id' => $data['course_id'],
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        return redirect()->route('student.enrollments.index')->with('success', 'Matrícula completada correctamente.');
    }

    public function destroy(Enrollment $enrollment)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user?->isSuperAdmin() && $enrollment->student_id !== Auth::id()) {
            abort(403);
        }

        $enrollment->delete();

        return back()->with('success', 'Matrícula eliminada correctamente.');
    }
}
