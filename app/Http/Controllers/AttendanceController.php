<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRecordRequest;
use App\Http\Requests\UpdateAttendanceRecordRequest;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Services\AttendancePeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(AttendancePeriodService $service)
    {
        $teacher = Auth::user();
        $courses = Course::where('professor_id', $teacher->id)->orderBy('name')->get();
        $last30days = $service->getLast30DaysStats($teacher->id);
        $current = $service->getCurrentQuincena();
        $lastClosed = $service->getLastClosedQuincena();

        $currentSessionCount = AttendanceRecord::forTeacher($teacher->id)
            ->whereBetween('attendance_date', [$current['start']->toDateString(), $current['end']->toDateString()])
            ->count();

        $currentAssistants = AttendanceRecord::forTeacher($teacher->id)
            ->whereBetween('attendance_date', [$current['start']->toDateString(), $current['end']->toDateString()])
            ->sum('students_present');

        $lastClosedSessionCount = AttendanceRecord::forTeacher($teacher->id)
            ->whereBetween('attendance_date', [$lastClosed['start'], $lastClosed['end']])
            ->count();

        $lastClosedAssistants = AttendanceRecord::forTeacher($teacher->id)
            ->whereBetween('attendance_date', [$lastClosed['start'], $lastClosed['end']])
            ->sum('students_present');

        $attendances = AttendanceRecord::with('course')
            ->forTeacher($teacher->id)
            ->latest('attendance_date')
            ->paginate(12);

        return view('teacher.attendance.index', compact('courses', 'last30days', 'current', 'lastClosed', 'attendances', 'currentSessionCount', 'currentAssistants', 'lastClosedSessionCount', 'lastClosedAssistants'));
    }

    public function create()
    {
        $teacher = Auth::user();
        $courses = Course::where('professor_id', $teacher->id)->orderBy('name')->get();

        return view('teacher.attendance.create', compact('courses'));
    }

    public function store(StoreAttendanceRecordRequest $request): RedirectResponse
    {
        $teacherId = Auth::id();

        AttendanceRecord::create(array_merge($request->validated(), [
            'teacher_id' => $teacherId,
        ]));

        return redirect()->route('teacher.attendance.index')->with('success', 'Asistencia registrada correctamente.');
    }

    public function edit(AttendanceRecord $attendance)
    {
        if ($attendance->teacher_id !== Auth::id()) {
            abort(403);
        }

        $courses = Course::where('professor_id', Auth::id())->orderBy('name')->get();

        return view('teacher.attendance.edit', compact('attendance', 'courses'));
    }

    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendance): RedirectResponse
    {
        if ($attendance->teacher_id !== Auth::id()) {
            abort(403);
        }

        $attendance->update($request->validated());

        return redirect()->route('teacher.attendance.index')->with('success', 'Asistencia actualizada correctamente.');
    }

    public function destroy(AttendanceRecord $attendance): RedirectResponse
    {
        if ($attendance->teacher_id !== Auth::id()) {
            abort(403);
        }

        $attendance->delete();

        return redirect()->route('teacher.attendance.index')->with('success', 'Registro de asistencia eliminado correctamente.');
    }
}
