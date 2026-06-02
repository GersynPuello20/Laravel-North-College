<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseManagementController extends Controller
{
    // Para docentes: listar sus cursos
    public function index()
    {
        if (! auth()->user()->isTeacher()) {
            abort(403);
        }

        $courses = Course::all(); // Debería filtrarse por docente

        return view('courses.teacher.index', [
            'courses' => $courses,
        ]);
    }

    // Para docentes: crear nuevo curso
    public function create()
    {
        if (! auth()->user()->isTeacher()) {
            abort(403);
        }

        return view('courses.teacher.create');
    }

    // Para docentes: guardar nuevo curso
    public function store(Request $request)
    {
        if (! auth()->user()->isTeacher()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:courses,code',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $validated['teacher_id'] = auth()->id();

        Course::create($validated);

        return redirect()->route('teacher.courses.index')
            ->with('success', 'Curso creado exitosamente.');
    }

    // Para docentes: editar curso
    public function edit(Course $course)
    {
        if (! auth()->user()->isTeacher() || $course->teacher_id !== auth()->id()) {
            abort(403);
        }

        return view('courses.teacher.edit', [
            'course' => $course,
        ]);
    }

    // Para docentes: actualizar curso
    public function update(Request $request, Course $course)
    {
        if (! auth()->user()->isTeacher() || $course->teacher_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $course->update($validated);

        return redirect()->route('teacher.courses.index')
            ->with('success', 'Curso actualizado exitosamente.');
    }

    // Para docentes: eliminar curso
    public function destroy(Course $course)
    {
        if (! auth()->user()->isTeacher() || $course->teacher_id !== auth()->id()) {
            abort(403);
        }

        $course->delete();

        return redirect()->route('teacher.courses.index')
            ->with('success', 'Curso eliminado exitosamente.');
    }
}
