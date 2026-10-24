<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('course')->latest()->paginate(15);

        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        $courses = Course::orderBy('name')->get();

        return view('subjects.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['created_by'] = Auth::id();

        Subject::create($data);

        return redirect()->route('admin.subjects.index')->with('success', 'Asignatura creada correctamente.');
    }

    public function edit(Subject $subject)
    {
        $courses = Course::orderBy('name')->get();

        return view('subjects.edit', compact('subject', 'courses'));
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['updated_by'] = Auth::id();

        $subject->update($data);

        return redirect()->route('admin.subjects.index')->with('success', 'Asignatura actualizada correctamente.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Asignatura eliminada correctamente.');
    }
}
