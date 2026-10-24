<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de asistencia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 16px; color: #0f2c59; margin: 0; }
        .muted { color: #64748b; }
        .summary { margin: 12px 0; }
        .summary span { display: inline-block; margin-right: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f2c59; color: #fff; text-align: left; padding: 5px; font-size: 9px; }
        td { border-bottom: 1px solid #e2e8f0; padding: 4px 5px; vertical-align: top; }
    </style>
</head>
<body>
    <h1>North College · Reporte de asistencia</h1>
    <p class="muted">Del {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }} · Generado {{ now()->format('d/m/Y H:i') }} por {{ $generatedBy->name }}</p>

    <div class="summary">
        <span><strong>Asistencia:</strong> {{ $rate !== null ? $rate.' %' : '—' }}</span>
        @foreach (\App\Models\SessionAttendance::STATUS_LABELS as $status => $label)
            <span><strong>{{ $label }}:</strong> {{ $totals[$status] }}</span>
        @endforeach
    </div>

    @if ($truncated)
        <p class="muted">El PDF muestra los primeros 2000 registros. Usa Excel o CSV para el listado completo.</p>
    @endif

    <table>
        <thead>
            <tr><th>Fecha</th><th>Grupo</th><th>Tutor</th><th>Estudiante</th><th>Estado</th><th>Método</th><th>Registrado por</th><th>Corrección</th></tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                @php $last = $record->corrections->first(); @endphp
                <tr>
                    <td>{{ $record->classSession->starts_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $record->classSession->course?->name }}</td>
                    <td>{{ $record->classSession->course?->professor?->name }}</td>
                    <td>{{ $record->student?->name }}</td>
                    <td>{{ $record->statusLabel() }}</td>
                    <td>{{ $record->methodLabel() }}</td>
                    <td>{{ $record->recordedBy?->name }}</td>
                    <td>{{ $last ? $last->changedBy?->name.': '.$last->reason : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Sin registros en el periodo.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
