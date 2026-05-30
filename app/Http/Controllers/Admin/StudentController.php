<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::with('role')
            ->whereHas('role', fn ($query) => $query->where('slug', 'student'))
            ->latest()
            ->paginate(15);

        return view('students.index', compact('students'));
    }

    public function create()
    {
        return redirect()->route('users.index')->with('info', 'Los estudiantes se gestionan desde Usuarios.');
    }

    public function store(Request $request)
    {
        return redirect()->route('users.index')->with('info', 'La creación de estudiantes se realiza desde Usuarios.');
    }

    public function edit(User $student)
    {
        return redirect()->route('users.index')->with('info', 'La edición de estudiantes se realiza desde Usuarios.');
    }

    public function update(Request $request, User $student)
    {
        return redirect()->route('users.index')->with('info', 'La edición de estudiantes se realiza desde Usuarios.');
    }

    public function destroy(User $student)
    {
        return redirect()->route('users.index')->with('info', 'La eliminación de estudiantes se realiza desde Usuarios.');
    }
}
