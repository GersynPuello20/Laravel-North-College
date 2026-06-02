<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    // Para docentes: listar estudiantes a calificar
    public function index()
    {
        if (! auth()->user()->isTeacher()) {
            abort(403);
        }

        // Obtener estudiantes del docente (relacionados con cursos del docente)
        $students = Student::all();

        return view('grades.index', [
            'students' => $students,
        ]);
    }

    // Para estudiantes: ver sus calificaciones
    public function studentGrades()
    {
        $student = auth()->user()->student;

        if (! $student) {
            abort(403);
        }

        // Aquí iría la lógica para obtener calificaciones del estudiante
        // Por ahora es un placeholder
        $grades = [];

        return view('grades.student', [
            'student' => $student,
            'grades' => $grades,
        ]);
    }

    // Para docentes: agregar calificación
    public function store(Request $request)
    {
        if (! auth()->user()->isTeacher()) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject' => 'required|string',
            'grade' => 'required|numeric|min:0|max:5',
            'notes' => 'nullable|string',
        ]);

        // Guardar calificación en la base de datos
        // Aquí necesitarías una tabla de grades

        return redirect()->back()->with('success', 'Calificación guardada correctamente.');
    }
}
