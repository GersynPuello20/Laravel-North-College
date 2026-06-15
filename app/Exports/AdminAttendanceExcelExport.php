<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminAttendanceExcelExport implements FromCollection, WithHeadings
{
    use Exportable;

    protected Collection $attendances;
    protected $teacher;
    protected array $period;

    public function __construct(Collection $attendances, $teacher, array $period)
    {
        $this->attendances = $attendances;
        $this->teacher = $teacher;
        $this->period = $period;
    }

    public function collection()
    {
        return $this->attendances->map(fn ($attendance) => [
            'Profesor' => $attendance->teacher?->name ?? 'N/A',
            'Curso' => $attendance->course->name,
            'Fecha' => $attendance->attendance_date->format('Y-m-d'),
            'Hora' => $attendance->class_time,
            'Asistentes' => $attendance->students_present,
            'Observaciones' => $attendance->observations,
        ]);
    }

    public function headings(): array
    {
        return ['Profesor', 'Curso', 'Fecha', 'Hora', 'Asistentes', 'Observaciones'];
    }
}
