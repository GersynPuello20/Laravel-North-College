@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-blue-950">Mis cursos</h1>
        <p class="text-gray-500 mt-2">Estos son los cursos que tienes asignados como docente.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($courses as $course)
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h2 class="text-2xl font-semibold text-blue-950">{{ $course->name }}</h2>
                <p class="mt-3 text-gray-600">{{ $course->description }}</p>
                <p class="mt-4 text-sm text-gray-500">Estudiantes matriculados: {{ $course->students_count }}</p>
            </div>
        @empty
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <p class="text-gray-500">No tienes cursos asignados aún.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
