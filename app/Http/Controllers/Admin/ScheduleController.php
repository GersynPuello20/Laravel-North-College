<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with('course')->latest()->paginate(20);

        return view('schedules.index', compact('schedules'));
    }

    public function create()
    {
        $courses = Course::orderBy('name')->get();

        return view('schedules.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|string|max:20',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'classroom' => 'required|string|max:80',
        ]);

        Schedule::create($data);

        return redirect()->route('admin.schedules.index')->with('success', 'Horario guardado correctamente.');
    }

    public function edit(Schedule $schedule)
    {
        $courses = Course::orderBy('name')->get();

        return view('schedules.edit', compact('schedule', 'courses'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|string|max:20',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'classroom' => 'required|string|max:80',
        ]);

        $schedule->update($data);

        return redirect()->route('admin.schedules.index')->with('success', 'Horario actualizado correctamente.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('admin.schedules.index')->with('success', 'Horario eliminado correctamente.');
    }
}
