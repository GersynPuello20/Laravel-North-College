@extends('layouts.app')

@section('title', 'Nueva sesión')

@section('content')
    <x-page-header title="Nueva sesión de clase" subtitle="La sesión se crea como programada; ábrela cuando empiece la clase." :back="route('sessions.index')" />

    @if ($courses->isEmpty())
        <div class="card">
            <x-empty-state icon="user-group" title="No tienes grupos disponibles">
                Un administrador debe asignarte como tutor de un grupo para crear sesiones.
            </x-empty-state>
        </div>
    @else
        <form method="POST" action="{{ route('sessions.store') }}" class="card max-w-3xl">
            @csrf
            <div class="card-body grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-field label="Grupo" name="course_id" required class="sm:col-span-2">
                    <select id="course_id" name="course_id" class="form-input" required>
                        <option value="">Selecciona un grupo</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected(old('course_id', $selectedCourse) == $course->id)>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </x-field>

                <x-field label="Fecha" name="session_date" required>
                    <input id="session_date" type="date" name="session_date" value="{{ old('session_date', today()->toDateString()) }}" class="form-input" required>
                </x-field>

                <x-field label="Tolerancia para llegar tarde" name="late_after_minutes" hint="Después de estos minutos desde el inicio, el registro queda como «tarde»." required>
                    <div class="flex items-center gap-2">
                        <input id="late_after_minutes" type="number" min="0" max="120" name="late_after_minutes" value="{{ old('late_after_minutes', 10) }}" class="form-input w-28" required>
                        <span class="text-sm text-slate-500">minutos</span>
                    </div>
                </x-field>

                <x-field label="Hora de inicio" name="start_time" required>
                    <input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}" class="form-input" required>
                </x-field>

                <x-field label="Hora de fin" name="end_time" required>
                    <input id="end_time" type="time" name="end_time" value="{{ old('end_time') }}" class="form-input" required>
                </x-field>

                <x-field label="Tema (opcional)" name="topic" class="sm:col-span-2">
                    <input id="topic" type="text" name="topic" value="{{ old('topic') }}" maxlength="255" class="form-input">
                </x-field>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
                <a href="{{ route('sessions.index') }}" class="btn-secondary">Cancelar</a>
                <button class="btn-primary">Crear sesión</button>
            </div>
        </form>
    @endif
@endsection
