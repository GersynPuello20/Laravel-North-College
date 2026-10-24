<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::with(['student', 'subject.course', 'createdBy'])->latest()->paginate(20);

        return view('grades.index', compact('grades'));
    }

    public function teacherIndex()
    {
        $grades = Grade::with(['student', 'subject.course'])
            ->whereHas('subject.course', fn ($query) => $query->where('professor_id', Auth::id()))
            ->latest()
            ->paginate(20);

        return view('grades.teacher_index', compact('grades'));
    }

    public function create()
    {
        return view('grades.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = Auth::id();

        Grade::create($data);

        return redirect()->route('teacher.grades.index')->with('success', 'Nota registrada correctamente.');
    }

    public function edit(Grade $grade)
    {
        $this->ensureOwnGrade($grade);

        return view('grades.edit', array_merge($this->formData(), compact('grade')));
    }

    public function update(Request $request, Grade $grade)
    {
        $this->ensureOwnGrade($grade);

        $data = $this->validated($request);
        $data['updated_by'] = Auth::id();

        $grade->update($data);

        return redirect()->route('teacher.grades.index')->with('success', 'Nota actualizada correctamente.');
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();

        return redirect()->route('admin.grades.index')->with('success', 'Nota eliminada correctamente.');
    }

    /** El docente solo puede tocar notas de asignaturas de sus grupos. */
    private function ensureOwnGrade(Grade $grade): void
    {
        abort_unless($grade->subject?->course?->professor_id === Auth::id(), 403, 'Esta nota no pertenece a tus grupos.');
    }

    private function validated(Request $request): array
    {
        $ownSubjects = Subject::whereHas('course', fn ($q) => $q->where('professor_id', Auth::id()))->pluck('id');

        $data = $request->validate([
            'student_id' => ['required', Rule::exists('users', 'id')],
            'subject_id' => ['required', Rule::in($ownSubjects)],
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'observations' => ['nullable', 'string', 'max:500'],
        ]);

        $subject = Subject::find($data['subject_id']);
        $enrolled = $subject->course->students()->where('users.id', $data['student_id'])->exists();
        if (! $enrolled) {
            throw ValidationException::withMessages(['student_id' => 'El estudiante no está matriculado en el grupo de esa asignatura.']);
        }

        return $data;
    }

    private function formData(): array
    {
        $subjects = Subject::with('course')
            ->whereHas('course', fn ($query) => $query->where('professor_id', Auth::id()))
            ->orderBy('name')
            ->get();

        $students = User::students()
            ->whereHas('enrollments.course', fn ($q) => $q->where('professor_id', Auth::id()))
            ->orderBy('name')
            ->get();

        return compact('students', 'subjects');
    }
}
