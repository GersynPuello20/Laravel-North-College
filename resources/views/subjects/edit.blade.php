@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-blue-950">Editar asignatura</h1>
        <p class="text-gray-500 mt-2">Actualiza los datos de la asignatura vinculada al curso.</p>
    </div>

    <div class="rounded-3xl bg-white p-8 shadow-lg">
        <form action="{{ route('admin.subjects.update', $subject) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700">Curso</label>
                <select name="course_id" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                    <option value="">Selecciona un curso</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected(old('course_id', $subject->course_id) == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $subject->name) }}" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Descripción</label>
                <textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900">{{ old('description', $subject->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <button type="submit" class="rounded-xl bg-blue-950 px-6 py-3 text-white font-semibold hover:bg-red-600 transition">Actualizar asignatura</button>
        </form>
    </div>
</div>
@endsection
