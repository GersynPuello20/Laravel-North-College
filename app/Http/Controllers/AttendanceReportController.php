<?php

namespace App\Http\Controllers;

use App\Exports\AdminAttendanceExcelExport;
use App\Exports\AdminAttendancePdfExport;
use App\Exports\TeacherAttendanceExcelExport;
use App\Exports\TeacherAttendancePdfExport;
use App\Http\Requests\AdminAttendanceReportRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\AttendancePeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function teacherReport(Request $request, AttendancePeriodService $service)
    {
        $teacher = Auth::user();
        $period = $this->resolveTeacherPeriod($request, $service);
        $attendances = AttendanceRecord::with('course')
            ->forTeacher($teacher->id)
            ->inPeriod($period['start'], $period['end'])
            ->orderBy('attendance_date')
            ->get();

        $summary = $this->buildSummary($attendances);

        return view('teacher.attendance.report', compact('teacher', 'period', 'attendances', 'summary'));
    }

    public function teacherPdf(Request $request, AttendancePeriodService $service)
    {
        $teacher = Auth::user();
        $period = $this->resolveTeacherPeriod($request, $service);
        $attendances = AttendanceRecord::with('course')
            ->forTeacher($teacher->id)
            ->inPeriod($period['start'], $period['end'])
            ->orderBy('attendance_date')
            ->get();

        $summary = $this->buildSummary($attendances);
        $filename = sprintf('Reporte_Asistencia_%s_%s.pdf', str_replace(' ', '_', $teacher->name), Carbon::now()->format('Y_m_d'));

        return (new TeacherAttendancePdfExport($teacher, $attendances, $period, $summary))->download($filename);
    }

    public function teacherExcel(Request $request, AttendancePeriodService $service)
    {
        $teacher = Auth::user();
        $period = $this->resolveTeacherPeriod($request, $service);
        $attendances = AttendanceRecord::with('course')
            ->forTeacher($teacher->id)
            ->inPeriod($period['start'], $period['end'])
            ->orderBy('attendance_date')
            ->get();

        $filename = sprintf('Reporte_Asistencia_%s_%s.xlsx', str_replace(' ', '_', $teacher->name), Carbon::now()->format('Y_m_d'));

        return Excel::download(new TeacherAttendanceExcelExport($attendances, $teacher, $period), $filename);
    }

    public function adminIndex(AdminAttendanceReportRequest $request, AttendancePeriodService $service)
    {
        $teachers = User::whereHas('role', fn ($query) => $query->where('slug', 'teacher'))
            ->orderBy('name')
            ->get();

        $currentPeriod = $service->getCurrentQuincena();
        $periods = $service->getClosedPeriods(4);
        $selectedTeacherId = $request->input('teacher_id');
        $period = $service->parsePeriodKey($request->input('period_key')) ?? $service->getLastClosedQuincena();

        $query = AttendanceRecord::with(['course', 'teacher'])
            ->when($selectedTeacherId, fn ($query) => $query->where('teacher_id', $selectedTeacherId))
            ->inPeriod($period['start'], $period['end']);

        $attendances = $query->orderBy('attendance_date')->get();
        $summary = $this->buildSummary($attendances);

        return view('admin.attendance_reports.index', compact('teachers', 'periods', 'currentPeriod', 'period', 'attendances', 'summary', 'selectedTeacherId'));
    }

    public function adminPdf(AdminAttendanceReportRequest $request, AttendancePeriodService $service)
    {
        $teacherId = $request->input('teacher_id');
        $teacher = $teacherId ? User::find($teacherId) : null;
        $period = $service->parsePeriodKey($request->input('period_key')) ?? $service->getLastClosedQuincena();
        $attendances = AttendanceRecord::with(['course', 'teacher'])
            ->when($teacherId, fn ($query) => $query->where('teacher_id', $teacherId))
            ->inPeriod($period['start'], $period['end'])
            ->orderBy('attendance_date')
            ->get();

        $summary = $this->buildSummary($attendances);
        $filename = 'Reporte_Asistencia_Administrativo_' . Carbon::now()->format('Y_m_d') . '.pdf';

        return (new AdminAttendancePdfExport($teacher, $attendances, $period, $summary))->download($filename);
    }

    public function adminExcel(AdminAttendanceReportRequest $request, AttendancePeriodService $service)
    {
        $teacherId = $request->input('teacher_id');
        $teacher = $teacherId ? User::find($teacherId) : null;
        $period = $service->parsePeriodKey($request->input('period_key')) ?? $service->getLastClosedQuincena();
        $attendances = AttendanceRecord::with(['course', 'teacher'])
            ->when($teacherId, fn ($query) => $query->where('teacher_id', $teacherId))
            ->inPeriod($period['start'], $period['end'])
            ->orderBy('attendance_date')
            ->get();

        $filename = 'Reporte_Asistencia_Administrativo_' . Carbon::now()->format('Y_m_d') . '.xlsx';

        return Excel::download(new AdminAttendanceExcelExport($attendances, $teacher, $period), $filename);
    }

    private function resolveTeacherPeriod(Request $request, AttendancePeriodService $service): array
    {
        return $request->query('period') === 'current'
            ? $service->getCurrentQuincena()
            : $service->getLastClosedQuincena();
    }

    private function buildSummary($attendances): array
    {
        $totalClasses = $attendances->count();
        $totalAssistants = $attendances->sum('students_present');
        $totalCourses = $attendances->pluck('course_id')->unique()->count();
        $averageAttendance = $totalClasses > 0 ? round($totalAssistants / $totalClasses, 2) : 0;

        return compact('totalClasses', 'totalAssistants', 'totalCourses', 'averageAttendance');
    }
}
