@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-blue-950">Registrar nota</h1>
        <p class="text-gray-500 mt-2">Ingresa la nota de un estudiante para una asignatura específica.</p>
    </div>

    <div class="rounded-3xl bg-white p-8 shadow-lg">
        <form action="{{ route('teacher.grades.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700">Estudiante</label>
                <select name="student_id" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                    <option value="">Selecciona un estudiante</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Asignatura</label>
                <select name="subject_id" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                    <option value="">Selecciona una asignatura</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                            {{ $subject->name }} - {{ $subject->course?->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('subject_id')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Nota</label>
                <input type="number" name="grade" step="0.01" min="0" max="100" value="{{ old('grade') }}" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                <x-input-error :messages="$errors->get('grade')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Observaciones</label>
                <textarea name="observations" rows="4" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900">{{ old('observations') }}</textarea>
                <x-input-error :messages="$errors->get('observations')" class="mt-2" />
            </div>

            <button type="submit" class="rounded-xl bg-blue-950 px-6 py-3 text-white font-semibold hover:bg-red-600 transition">Guardar nota</button>
        </form>
    </div>
</div>
@endsection
