<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // Para estudiantes: ver su horario
    public function studentSchedule()
    {
        $student = auth()->user()->student;

        if (! $student) {
            abort(403);
        }

        // Obtener horarios/materias del estudiante
        $schedule = Subject::all(); // Esto debería filtrarse según el curso del estudiante

        return view('schedule.student', [
            'student' => $student,
            'schedule' => $schedule,
        ]);
    }

    // Para docentes: ver estudiantes en sus materias
    public function teacherSchedule()
    {
        if (! auth()->user()->isTeacher()) {
            abort(403);
        }

        // Obtener horarios y materias del docente
        $subjects = Subject::all(); // Debería filtrarse según el docente

        return view('schedule.teacher', [
            'subjects' => $subjects,
        ]);
    }
}
