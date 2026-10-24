<?php

namespace App\Exports;

use App\Models\SessionAttendance;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['Fecha', 'Hora inicio', 'Grupo', 'Tutor', 'Estudiante', 'Correo', 'Estado', 'Método', 'Registrado a las', 'Registrado por', 'Similitud', 'Correcciones', 'Última corrección'];
    }

    /** @param  SessionAttendance  $row */
    public function map($row): array
    {
        $session = $row->classSession;
        $last = $row->corrections->first();

        return [
            $session->session_date->format('Y-m-d'),
            $session->starts_at->format('H:i'),
            $session->course?->name,
            $session->course?->professor?->name,
            $row->student?->name,
            $row->student?->email,
            $row->statusLabel(),
            $row->methodLabel(),
            $row->recorded_at->format('Y-m-d H:i'),
            $row->recordedBy?->name,
            $row->match_score,
            $row->corrections->count(),
            $last ? "{$last->changedBy?->name}: {$last->reason}" : '',
        ];
    }
}
