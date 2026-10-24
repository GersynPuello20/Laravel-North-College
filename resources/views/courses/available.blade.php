@extends('layouts.app')

@section('title', 'Cursos disponibles')

@section('content')
    <x-page-header title="Cursos disponibles" subtitle="Grupos activos en los que todavía no estás matriculado." />

    <div class="card">
        @if ($availableCourses->isEmpty())
            <x-empty-state icon="book-open" title="No hay cursos disponibles por ahora" />
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($availableCourses as $course)
                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">{{ $course->name }}</p>
                            <p class="text-xs text-slate-500">Tutor: {{ $course->professor?->name ?? 'Sin asignar' }}{{ $course->description ? ' · '.$course->description : '' }}</p>
                        </div>
                        <form method="POST" action="{{ route('student.enrollments.store') }}">
                            @csrf
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            <button class="btn-primary btn-sm">Matricularme</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            {{ $availableCourses->links() }}
        @endif
    </div>
@endsection
