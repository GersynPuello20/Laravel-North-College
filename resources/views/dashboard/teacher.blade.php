@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-blue-950">Dashboard Profesor</h1>
    <p class="text-gray-500 mt-2">Accede a tus cursos asignados, estudiantes y gestión de notas.</p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-blue-900">
        <p class="text-gray-500">Cursos asignados</p>
        <h2 class="text-4xl font-bold mt-3">{{ $courses->count() }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-red-600">
        <p class="text-gray-500">Estudiantes matriculados</p>
        <h2 class="text-4xl font-bold mt-3">{{ $students }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-green-500">
        <p class="text-gray-500">Notas registradas</p>
        <h2 class="text-4xl font-bold mt-3">{{ $gradeCount }}</h2>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-2 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-blue-950 mb-4">Resumen asistencia últimos 30 días</h2>
        <p class="text-gray-600">Clases impartidas: <span class="font-semibold">{{ $attendanceStats['total_classes'] }}</span></p>
        <p class="text-gray-600">Estudiantes atendidos: <span class="font-semibold">{{ $attendanceStats['total_assistants'] }}</span></p>
        <p class="text-gray-600">Cursos impactados: <span class="font-semibold">{{ $attendanceStats['total_courses'] }}</span></p>
        <p class="text-gray-600">Promedio asistencia: <span class="font-semibold">{{ $attendanceStats['average_attendance'] }}</span></p>
    </div>
    <div class="bg-white rounded-3xl p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-blue-950 mb-4">Quincenas</h2>
        <div class="space-y-3">
            <div class="rounded-2xl border border-slate-200 p-4">
                <p class="text-gray-500">Quincena actual</p>
                <p class="font-semibold mt-1">{{ $current['label'] }}</p>
                <p class="text-sm text-amber-700 mt-1">{{ $current['status'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 p-4">
                <p class="text-gray-500">Última quincena cerrada</p>
                <p class="font-semibold mt-1">{{ $lastClosed['label'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-3xl p-6 shadow-sm">
    <h2 class="text-2xl font-bold text-blue-950 mb-4">Últimos cursos</h2>
    <div class="space-y-4">
        @forelse($courses as $course)
            <div class="rounded-2xl border border-slate-200 p-4">
                <h3 class="font-semibold text-lg">{{ $course->name }}</h3>
                <p class="text-gray-500 mt-1">{{ $course->description }}</p>
            </div>
        @empty
            <p class="text-gray-500">No tienes cursos asignados.</p>
        @endforelse
    </div>
</div>
@endsection
