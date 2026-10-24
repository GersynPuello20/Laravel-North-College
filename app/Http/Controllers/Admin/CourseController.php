<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('professor')->latest()->paginate(15);

        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        $teachers = User::whereHas('role', fn ($query) => $query->where('slug', 'teacher'))
            ->orderBy('name')
            ->get();

        return view('courses.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'professor_id' => 'nullable|exists:users,id',
        ]);

        $data['created_by'] = Auth::id();
        $data['status'] = 'active';

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('success', 'Curso creado correctamente.');
    }

    public function edit(Course $course)
    {
        $teachers = User::whereHas('role', fn ($query) => $query->where('slug', 'teacher'))
            ->orderBy('name')
            ->get();

        return view('courses.edit', compact('course', 'teachers'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'professor_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,pending,suspended',
        ]);

        $data['updated_by'] = Auth::id();

        $course->update($data);

        return redirect()->route('admin.courses.index')->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Curso eliminado correctamente.');
    }

    public function teacherIndex()
    {
        $courses = Course::withCount('students')
            ->where('professor_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('courses.teacher_index', compact('courses'));
    }

    public function available()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Acceso no autorizado.');
        }

        $enrolledCourseIds = $user->enrollments()->pluck('course_id')->toArray();

        $availableCourses = Course::with('professor')
            ->whereNotIn('id', $enrolledCourseIds)
            ->where('status', 'active')
            ->latest()
            ->paginate(15);

        return view('courses.available', compact('availableCourses'));
    }
}
