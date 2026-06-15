@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-blue-950">Reportes generales de asistencia</h1>
    <p class="text-gray-500 mt-2">Genera reportes quincenales para un profesor o para toda la institución.</p>
</div>

@php
    $exportQuery = array_filter(request()->query(), fn ($value) => $value !== '');
@endphp

<form action="{{ route('admin.attendance.reports.index') }}" method="GET" class="mb-8 grid gap-4 lg:grid-cols-3">
    <div>
        <label class="text-sm font-medium text-slate-700">Profesor</label>
        @php $currentTeacherId = old('teacher_id', $selectedTeacherId); @endphp
        <select name="teacher_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3">
            <option value="">Todos los profesores</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" {{ $currentTeacherId == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
            @endforeach
        </select>
        @error('teacher_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700">Quincena</label>
        <select name="period_key" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3">
            <option value="current" {{ request('period_key') === 'current' ? 'selected' : '' }}>{{ $currentPeriod['label'] }} (Actual)</option>
            @foreach($periods as $item)
                <option value="{{ $item['start'] }}_{{ $item['end'] }}" {{ request('period_key') === $item['start'].'_'.$item['end'] ? 'selected' : '' }}>{{ $item['label'] }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-end gap-3">
        <button type="submit" class="rounded-xl bg-blue-950 text-white px-5 py-3 text-sm font-semibold hover:bg-blue-800">Actualizar filtro</button>
        <a href="{{ route('admin.attendance.reports.pdf', $exportQuery) }}" class="rounded-xl bg-red-600 text-white px-5 py-3 text-sm font-semibold hover:bg-red-500">Exportar PDF</a>
        <a href="{{ route('admin.attendance.reports.excel', $exportQuery) }}" class="rounded-xl bg-green-600 text-white px-5 py-3 text-sm font-semibold hover:bg-green-500">Exportar Excel</a>
    </div>
</form>

<div class="grid gap-6 xl:grid-cols-4 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-blue-900">
        <p class="text-gray-500">Profesores incluidos</p>
        <h2 class="text-4xl font-bold mt-3">{{ $teachers->count() }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-red-600">
        <p class="text-gray-500">Sesiones en periodo</p>
        <h2 class="text-4xl font-bold mt-3">{{ $summary['totalClasses'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-yellow-500">
        <p class="text-gray-500">Total asistentes</p>
        <h2 class="text-4xl font-bold mt-3">{{ $summary['totalAssistants'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-green-500">
        <p class="text-gray-500">Promedio institucional</p>
        <h2 class="text-4xl font-bold mt-3">{{ $summary['averageAttendance'] }}</h2>
    </div>
</div>

<div class="overflow-hidden rounded-3xl bg-white shadow-sm">
    <table class="w-full text-left border-separate border-spacing-0">
        <thead class="bg-slate-100 text-slate-600 uppercase text-xs tracking-wide">
            <tr>
                <th class="p-4">Profesor</th>
                <th class="p-4">Curso</th>
                <th class="p-4">Fecha</th>
                <th class="p-4">Hora</th>
                <th class="p-4">Asistentes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
                <tr class="border-t border-slate-200">
                    <td class="p-4">{{ $attendance->teacher?->name ?? 'N/A' }}</td>
                    <td class="p-4">{{ $attendance->course->name }}</td>
                    <td class="p-4">{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                    <td class="p-4">{{ $attendance->class_time }}</td>
                    <td class="p-4">{{ $attendance->students_present }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-6 text-center text-gray-500">No se encontraron registros para el periodo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
