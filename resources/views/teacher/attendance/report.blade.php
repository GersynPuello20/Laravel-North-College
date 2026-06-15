@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-blue-950">Reporte quincenal</h1>
    <p class="text-gray-500 mt-2">Soporte académico de la última quincena cerrada.</p>
</div>

<div class="grid gap-6 xl:grid-cols-2 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-blue-950">Datos del reporte</h2>
        <p class="text-gray-600 mt-4">Profesor: <span class="font-semibold">{{ $teacher->name }}</span></p>
        <p class="text-gray-600 mt-2">Periodo: <span class="font-semibold">{{ $period['label'] }}</span></p>
        <p class="text-gray-600 mt-2">Fecha de generación: <span class="font-semibold">{{ now()->format('d/m/Y') }}</span></p>
    </div>
    <div class="bg-white rounded-3xl p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-blue-950">Resumen del periodo</h2>
        <ul class="mt-4 space-y-3 text-gray-600">
            <li>Total de clases: <span class="font-semibold">{{ $summary['totalClasses'] }}</span></li>
            <li>Total de asistentes: <span class="font-semibold">{{ $summary['totalAssistants'] }}</span></li>
            <li>Promedio por clase: <span class="font-semibold">{{ $summary['averageAttendance'] }}</span></li>
            <li>Total de cursos: <span class="font-semibold">{{ $summary['totalCourses'] }}</span></li>
        </ul>
    </div>
</div>

<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('teacher.attendance.report.pdf') }}" class="rounded-xl bg-red-600 text-white px-5 py-3 text-sm font-semibold hover:bg-red-500">Descargar PDF</a>
    <a href="{{ route('teacher.attendance.report.excel') }}" class="rounded-xl bg-green-600 text-white px-5 py-3 text-sm font-semibold hover:bg-green-500">Descargar Excel</a>
    <a href="{{ route('teacher.attendance.index') }}" class="rounded-xl bg-slate-100 text-slate-700 px-5 py-3 text-sm font-semibold hover:bg-slate-200">Volver</a>
</div>

<div class="overflow-hidden rounded-3xl bg-white shadow-sm">
    <table class="w-full text-left border-separate border-spacing-0">
        <thead class="bg-slate-100 text-slate-600 uppercase text-xs tracking-wide">
            <tr>
                <th class="p-4">Fecha</th>
                <th class="p-4">Curso</th>
                <th class="p-4">Hora</th>
                <th class="p-4">Asistentes</th>
                <th class="p-4">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
                <tr class="border-t border-slate-200">
                    <td class="p-4">{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                    <td class="p-4">{{ $attendance->course->name }}</td>
                    <td class="p-4">{{ $attendance->class_time }}</td>
                    <td class="p-4">{{ $attendance->students_present }}</td>
                    <td class="p-4">{{ $attendance->observations }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-6 text-center text-gray-500">No se registraron clases en la última quincena cerrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
