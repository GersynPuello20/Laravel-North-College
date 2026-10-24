@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-blue-950">Editar horario</h1>
        <p class="text-gray-500 mt-2">Ajusta los detalles del horario del curso.</p>
    </div>

    <div class="rounded-3xl bg-white p-8 shadow-lg">
        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700">Curso</label>
                <select name="course_id" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                    <option value="">Selecciona un curso</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected(old('course_id', $schedule->course_id) == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Día de la semana</label>
                <input type="text" name="day_of_week" value="{{ old('day_of_week', $schedule->day_of_week) }}" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                <x-input-error :messages="$errors->get('day_of_week')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Hora de inicio</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $schedule->start_time) }}" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                    <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Hora de fin</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $schedule->end_time) }}" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                    <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Aula</label>
                <input type="text" name="classroom" value="{{ old('classroom', $schedule->classroom) }}" class="mt-2 w-full rounded-xl border border-gray-200 p-4 focus:border-blue-900 focus:ring-blue-900" required>
                <x-input-error :messages="$errors->get('classroom')" class="mt-2" />
            </div>

            <button type="submit" class="rounded-xl bg-blue-950 px-6 py-3 text-white font-semibold hover:bg-red-600 transition">Actualizar horario</button>
        </form>
    </div>
</div>
@endsection
