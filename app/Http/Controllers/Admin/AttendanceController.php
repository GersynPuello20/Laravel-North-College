<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user.role')->latest()->paginate(20);

        return view('attendances.index', compact('attendances'));
    }

    public function create()
    {
        $students = User::whereHas('role', fn ($query) => $query->where('slug', 'student'))
            ->orderBy('name')
            ->get();

        return view('attendances.create', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'attendance_type' => 'required|in:entry,exit',
            'recorded_at' => 'required|date',
            'note' => 'nullable|string|max:500',
        ]);

        Attendance::create($data);

        return redirect()->route('admin.attendances.index')->with('success', 'Registro de asistencia guardado correctamente.');
    }

    public function edit(Attendance $attendance)
    {
        $students = User::whereHas('role', fn ($query) => $query->where('slug', 'student'))
            ->orderBy('name')
            ->get();

        return view('attendances.edit', compact('attendance', 'students'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'attendance_type' => 'required|in:entry,exit',
            'recorded_at' => 'required|date',
            'note' => 'nullable|string|max:500',
        ]);

        $attendance->update($data);

        return redirect()->route('admin.attendances.index')->with('success', 'Registro de asistencia actualizado correctamente.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('admin.attendances.index')->with('success', 'Registro de asistencia eliminado correctamente.');
    }
}
