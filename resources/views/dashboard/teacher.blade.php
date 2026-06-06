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
