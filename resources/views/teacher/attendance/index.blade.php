@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-blue-950">Asistencia y Reportes</h1>
    <p class="text-gray-500 mt-2">Registra tus clases y genera el reporte de la última quincena cerrada.</p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 p-4 text-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="grid gap-6 xl:grid-cols-4 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-blue-900">
        <p class="text-gray-500">Clases últimos 30 días</p>
        <h2 class="text-4xl font-bold mt-3">{{ $last30days['total_classes'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-red-600">
        <p class="text-gray-500">Estudiantes atendidos</p>
        <h2 class="text-4xl font-bold mt-3">{{ $last30days['total_assistants'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-yellow-500">
        <p class="text-gray-500">Cursos impactados</p>
        <h2 class="text-4xl font-bold mt-3">{{ $last30days['total_courses'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-green-500">
        <p class="text-gray-500">Promedio por clase</p>
        <h2 class="text-4xl font-bold mt-3">{{ $last30days['average_attendance'] }}</h2>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-2 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-blue-950 mb-4">Quincena actual</h2>
        <p class="text-gray-600 mb-2">Periodo: <span class="font-semibold">{{ $current['label'] }}</span></p>
        <p class="text-gray-600 mb-2">Estado: <span class="font-semibold text-amber-700">{{ $current['status'] }}</span></p>
        <p class="text-gray-600">Registros: <span class="font-semibold">{{ $currentSessionCount }}</span></p>
        <p class="text-gray-600">Total asistentes: <span class="font-semibold">{{ $currentAssistants }}</span></p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('teacher.attendance.report', ['period' => 'current']) }}" class="rounded-xl bg-blue-950 text-white px-5 py-3 text-sm font-semibold hover:bg-blue-800">Ver reporte actual</a>
            <a href="{{ route('teacher.attendance.report.pdf', ['period' => 'current']) }}" class="rounded-xl bg-red-600 text-white px-5 py-3 text-sm font-semibold hover:bg-red-500">Descargar PDF</a>
            <a href="{{ route('teacher.attendance.report.excel', ['period' => 'current']) }}" class="rounded-xl bg-green-600 text-white px-5 py-3 text-sm font-semibold hover:bg-green-500">Descargar Excel</a>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-blue-950 mb-4">Última quincena cerrada</h2>
        <p class="text-gray-600 mb-2">Periodo: <span class="font-semibold">{{ $lastClosed['label'] }}</span></p>
        <p class="text-gray-600 mb-2">Clases registradas: <span class="font-semibold">{{ $lastClosedSessionCount }}</span></p>
        <p class="text-gray-600">Total asistentes: <span class="font-semibold">{{ $lastClosedAssistants }}</span></p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('teacher.attendance.report') }}" class="rounded-xl bg-blue-950 text-white px-5 py-3 text-sm font-semibold hover:bg-blue-800">Ver reporte</a>
            <a href="{{ route('teacher.attendance.report.pdf') }}" class="rounded-xl bg-red-600 text-white px-5 py-3 text-sm font-semibold hover:bg-red-500">Descargar PDF</a>
            <a href="{{ route('teacher.attendance.report.excel') }}" class="rounded-xl bg-green-600 text-white px-5 py-3 text-sm font-semibold hover:bg-green-500">Descargar Excel</a>
        </div>
    </div>
</div>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-semibold text-blue-950">Registros recientes</h2>
    <a href="{{ route('teacher.attendance.create') }}" class="rounded-xl bg-blue-950 text-white px-5 py-3 text-sm font-semibold hover:bg-blue-800">Registrar asistencia</a>
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
                <th class="p-4">Acciones</th>
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
                    <td class="p-4 space-x-2">
                        <a href="{{ route('teacher.attendance.edit', $attendance) }}" class="text-blue-950 hover:text-red-600">Editar</a>
                        <form action="{{ route('teacher.attendance.destroy', $attendance) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar este registro?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-500">No hay registros de asistencia aún.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $attendances->links() }}</div>
@endsection
