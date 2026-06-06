<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::with(['student.role', 'subject.course'])->latest()->paginate(20);

        return view('grades.index', compact('grades'));
    }

    public function teacherIndex()
    {
        $grades = Grade::with(['student.role', 'subject.course'])
            ->whereHas('subject.course', fn ($query) => $query->where('professor_id', Auth::id()))
            ->latest()
            ->paginate(20);

        return view('grades.teacher_index', compact('grades'));
    }

    public function create()
    {
        $students = User::whereHas('role', fn ($query) => $query->where('slug', 'student'))
            ->orderBy('name')
            ->get();

        $subjects = Subject::with('course')
            ->whereHas('course', fn ($query) => $query->where('professor_id', Auth::id()))
            ->orderBy('name')
            ->get();

        return view('grades.create', compact('students', 'subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade' => 'required|numeric|min:0|max:100',
            'observations' => 'nullable|string|max:500',
        ]);

        $data['created_by'] = Auth::id();

        Grade::create($data);

        return redirect()->route('teacher.grades.index')->with('success', 'Nota registrada correctamente.');
    }

    public function edit(Grade $grade)
    {
        $students = User::whereHas('role', fn ($query) => $query->where('slug', 'student'))
            ->orderBy('name')
            ->get();

        $subjects = Subject::with('course')
            ->whereHas('course', fn ($query) => $query->where('professor_id', Auth::id()))
            ->orderBy('name')
            ->get();

        return view('grades.edit', compact('grade', 'students', 'subjects'));
    }

    public function update(Request $request, Grade $grade)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade' => 'required|numeric|min:0|max:100',
            'observations' => 'nullable|string|max:500',
        ]);

        $data['updated_by'] = Auth::id();

        $grade->update($data);

        return redirect()->route('teacher.grades.index')->with('success', 'Nota actualizada correctamente.');
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();

        return redirect()->route('admin.grades.index')->with('success', 'Nota eliminada correctamente.');
    }
}
