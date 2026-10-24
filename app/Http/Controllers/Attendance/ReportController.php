<?php

namespace App\Http\Controllers\Attendance;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FaceRecognitionAttempt;
use App\Models\SessionAttendance;
use App\Models\User;
use App\Services\Attendance\AttendanceReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('view-attendance-reports');

        $filters = $this->filters($request);
        $report = new AttendanceReport($request->user(), $filters);
        $totals = $report->totals();

        return view('reports.index', [
            'filters' => $filters,
            'from' => $report->from(),
            'to' => $report->to(),
            'records' => $report->records()->paginate(25)->withQueryString(),
            'totals' => $totals,
            'rate' => $report->attendanceRate($totals),
            'failures' => $report->facialFailures(),
            'courses' => $this->courses($request->user()),
            'teachers' => $request->user()->isStaff() ? User::whereRole('teacher')->orderBy('name')->get(['id', 'name']) : collect(),
            'failureLabels' => FaceRecognitionAttempt::RESULT_LABELS,
        ]);
    }

    public function export(Request $request): Response
    {
        Gate::authorize('view-attendance-reports');

        $format = $request->validate(['format' => ['required', Rule::in(['xlsx', 'csv', 'pdf'])]])['format'];
        $filters = $this->filters($request);
        $report = new AttendanceReport($request->user(), $filters);
        $name = 'asistencia_'.$report->from()->format('Ymd').'_'.$report->to()->format('Ymd');

        if ($format === 'pdf') {
            $records = $report->records()->limit(2000)->get();
            $totals = $report->totals();

            return Pdf::loadView('reports.pdf', [
                'records' => $records,
                'totals' => $totals,
                'rate' => $report->attendanceRate($totals),
                'from' => $report->from(),
                'to' => $report->to(),
                'generatedBy' => $request->user(),
                'truncated' => $records->count() >= 2000,
            ])->setPaper('a4', 'landscape')->download("{$name}.pdf");
        }

        $type = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new AttendanceExport($report->records()), "{$name}.{$format}", $type);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'course_id' => ['nullable', 'integer'],
            'teacher_id' => ['nullable', 'integer'],
            'student' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(SessionAttendance::statuses())],
            'method' => ['nullable', Rule::in(array_keys(SessionAttendance::METHOD_LABELS))],
            'needs_review' => ['nullable', 'boolean'],
            'corrected' => ['nullable', 'boolean'],
        ]);
    }

    private function courses(User $user)
    {
        return Course::query()
            ->when(! $user->isStaff(), fn ($q) => $q->where('professor_id', $user->id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
