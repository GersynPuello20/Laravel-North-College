@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-blue-950">Registrar asistencia</h1>
    <p class="text-gray-500 mt-2">Registra una nueva sesión de clase para tu curso.</p>
</div>

@if($errors->any())
    <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-800">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('teacher.attendance.store') }}" method="POST" class="space-y-6 bg-white rounded-3xl p-6 shadow-sm">
    @csrf

    <div>
        <label class="text-sm font-medium text-slate-700">Curso</label>
        <select name="course_id" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3">
            <option value="">Selecciona un curso</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div>
            <label class="text-sm font-medium text-slate-700">Fecha</label>
            <input type="date" name="attendance_date" value="{{ old('attendance_date', now()->toDateString()) }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3" required>
        </div>

        <div>
            <label class="text-sm font-medium text-slate-700">Hora</label>
            <input type="time" name="class_time" value="{{ old('class_time') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3" required>
        </div>

        <div>
            <label class="text-sm font-medium text-slate-700">Asistentes</label>
            <input type="number" min="0" name="students_present" value="{{ old('students_present') }}" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3" required>
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-700">Observaciones</label>
        <textarea name="observations" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3">{{ old('observations') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-xl bg-blue-950 text-white px-6 py-3 font-semibold hover:bg-blue-800">Guardar asistencia</button>
        <a href="{{ route('teacher.attendance.index') }}" class="text-blue-950 hover:text-red-600">Cancelar</a>
    </div>
</form>
@endsection
