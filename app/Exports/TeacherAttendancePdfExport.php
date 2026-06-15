<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;

class TeacherAttendancePdfExport
{
    protected User $teacher;
    protected $attendances;
    protected array $period;
    protected array $summary;

    public function __construct(User $teacher, $attendances, array $period, array $summary)
    {
        $this->teacher = $teacher;
        $this->attendances = $attendances;
        $this->period = $period;
        $this->summary = $summary;
    }

    public function download(string $filename)
    {
        $pdf = Pdf::loadView('reports.teacher_attendance_pdf', [
            'teacher' => $this->teacher,
            'attendances' => $this->attendances,
            'period' => $this->period,
            'summary' => $this->summary,
        ]);

        return $pdf->download($filename);
    }
}
