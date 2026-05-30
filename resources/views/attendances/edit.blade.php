@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-10">
        <div class="rounded-3xl bg-white p-8 shadow-lg">
            <h1 class="text-3xl font-bold text-blue-950">Editar asistencia</h1>
            <p class="text-gray-500 mt-2">Actualiza los datos del registro de asistencia.</p>

            @if($errors->any())
                <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-700">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('attendances.update', $attendance) }}" method="POST" class="mt-8 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Estudiante</label>
                    <select name="user_id" class="mt-2 w-full rounded-xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected(old('user_id', $attendance->user_id) == $student->id)>{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tipo</label>
                    <select name="attendance_type" class="mt-2 w-full rounded-xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                        <option value="entry" @selected(old('attendance_type', $attendance->attendance_type) === 'entry')>Entrada</option>
                        <option value="exit" @selected(old('attendance_type', $attendance->attendance_type) === 'exit')>Salida</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Fecha y hora</label>
                    <input type="datetime-local" name="recorded_at" value="{{ old('recorded_at', $attendance->recorded_at?->format('Y-m-d\TH:i')) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nota opcional</label>
                    <textarea name="note" rows="3" class="mt-2 w-full rounded-xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500" placeholder="Comentario adicional">{{ old('note', $attendance->note) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <a href="{{ route('attendances.index') }}" class="rounded-xl border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="rounded-xl bg-blue-950 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
