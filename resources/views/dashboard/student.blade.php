@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-blue-950">Dashboard Estudiante</h1>
    <p class="text-gray-500 mt-2">Consulta tus cursos matriculados, horarios y calificaciones.</p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-blue-900">
        <p class="text-gray-500">Inscripciones recientes</p>
        <h2 class="text-4xl font-bold mt-3">{{ $enrollments->count() }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-green-500">
        <p class="text-gray-500">Calificaciones reportadas</p>
        <h2 class="text-4xl font-bold mt-3">{{ $grades->count() }}</h2>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-3xl p-6 shadow-sm">
        <h2 class="text-2xl font-bold text-blue-950 mb-4">Cursos matriculados</h2>
        <ul class="space-y-4">
            @forelse($enrollments as $enrollment)
                <li class="rounded-2xl border border-slate-200 p-4">
                    <p class="font-semibold">{{ $enrollment->course->name }}</p>
                    <p class="text-gray-500">Profesor: {{ $enrollment->course->professor?->name ?? 'Sin profesor' }}</p>
                    <p class="text-sm text-gray-400">Matriculado el {{ $enrollment->enrollment_date->format('d/m/Y') }}</p>
                </li>
            @empty
                <p class="text-gray-500">Aún no estás matriculado en ningún curso.</p>
            @endforelse
        </ul>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm">
        <h2 class="text-2xl font-bold text-blue-950 mb-4">Últimas calificaciones</h2>
        <ul class="space-y-4">
            @forelse($grades as $grade)
                <li class="rounded-2xl border border-slate-200 p-4">
                    <p class="font-semibold">{{ $grade->subject->name }} - {{ $grade->subject->course->name }}</p>
                    <p class="text-gray-500">Calificación: {{ $grade->grade }}</p>
                    <p class="text-sm text-gray-400">Observaciones: {{ $grade->observations ?? 'Ninguna' }}</p>
                </li>
            @empty
                <p class="text-gray-500">Aún no hay calificaciones registradas.</p>
            @endforelse
        </ul>
    </div>
</div>
@endsection
