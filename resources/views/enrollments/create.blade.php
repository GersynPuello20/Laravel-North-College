@extends('layouts.app')

@section('title', 'Matricularme')

@section('content')
    <x-page-header title="Matricularme en un curso" :back="route('student.enrollments.index')" />

    @if ($courses->isEmpty())
        <div class="card"><x-empty-state icon="book-open" title="No hay cursos disponibles" /></div>
    @else
        <form method="POST" action="{{ route('student.enrollments.store') }}" class="card max-w-xl">
            @csrf
            <div class="card-body">
                <x-field label="Curso" name="course_id" required>
                    <select id="course_id" name="course_id" class="form-input" required>
                        <option value="">Selecciona un curso</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }} · {{ $course->professor?->name ?? 'Sin tutor' }}</option>
                        @endforeach
                    </select>
                </x-field>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
                <a href="{{ route('student.enrollments.index') }}" class="btn-secondary">Cancelar</a>
                <button class="btn-primary">Matricularme</button>
            </div>
        </form>
    @endif
@endsection
