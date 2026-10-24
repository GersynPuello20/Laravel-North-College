@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-blue-950">Editar curso</h1>
        <p class="text-gray-500 mt-2">Actualiza la información del curso y su profesor asignado.</p>
    </div>

    <div class="rounded-3xl bg-white p-8 shadow-lg">
        <form action="{{ route('admin.courses.update', $course) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700">Nombre del curso</label>
                <input type="text" name="name" value="{{ old('name', $course->name) }}" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Profesor</label>
                <select name="professor_id" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900">
                    <option value="">Sin asignar</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('professor_id', $course->professor_id) == $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('professor_id')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Descripción</label>
                <textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900">{{ old('description', $course->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Estado</label>
                <select name="status" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900">
                    <option value="active" @selected(old('status', $course->status) === 'active')>Activo</option>
                    <option value="pending" @selected(old('status', $course->status) === 'pending')>Pendiente</option>
                    <option value="suspended" @selected(old('status', $course->status) === 'suspended')>Suspendido</option>
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>

            <button type="submit" class="rounded-xl bg-blue-950 px-6 py-3 text-white font-semibold hover:bg-red-600 transition">Actualizar curso</button>
        </form>
    </div>
</div>
@endsection
