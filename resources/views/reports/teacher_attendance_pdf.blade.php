<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .logo { font-size: 28px; font-weight: 800; color: #0f172a; }
        .subtitle { color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #d1d5db; padding: 10px; text-align: left; }
        th { background: #f8fafc; }
        .summary { margin-top: 30px; }
        .footer { margin-top: 40px; font-size: 12px; color: #475569; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="logo">North College</div>
            <p class="subtitle">Reporte de asistencia quincenal</p>
        </div>
        <div>
            <p>Profesor: <strong>{{ $teacher->name }}</strong></p>
            <p>Periodo: <strong>{{ $period['label'] }}</strong></p>
            <p>Generado: <strong>{{ now()->format('d/m/Y') }}</strong></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Curso</th>
                <th>Hora</th>
                <th>Asistentes</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                    <td>{{ $attendance->course->name }}</td>
                    <td>{{ $attendance->class_time }}</td>
                    <td>{{ $attendance->students_present }}</td>
                    <td>{{ $attendance->observations }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No se encontraron registros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h3>Resumen</h3>
        <p>Total de clases impartidas: <strong>{{ $summary['totalClasses'] }}</strong></p>
        <p>Total de asistentes: <strong>{{ $summary['totalAssistants'] }}</strong></p>
        <p>Promedio de asistencia: <strong>{{ $summary['averageAttendance'] }}</strong></p>
        <p>Total de cursos impartidos: <strong>{{ $summary['totalCourses'] }}</strong></p>
    </div>

    <div class="footer">
        <p>Este documento sirve como soporte académico para la elaboración de la cuenta de cobro correspondiente al periodo seleccionado.</p>
    </div>
</body>
</html>
