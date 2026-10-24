@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-blue-950">Matricularse en curso</h1>
        <p class="text-gray-500 mt-2">Selecciona el curso donde deseas inscribirte.</p>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm">
        @if(session('success'))
            <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6">
            @forelse($courses as $course)
                <div class="rounded-3xl border border-slate-200 p-6">
                    <h2 class="text-2xl font-semibold text-blue-950">{{ $course->name }}</h2>
                    <p class="text-gray-500 mt-2">{{ $course->description }}</p>
                    <p class="text-sm text-gray-400">Profesor: {{ $course->professor?->name ?? 'Sin profesor' }}</p>
                    <form action="{{ route('student.enrollments.store') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <button type="submit" class="rounded-xl bg-blue-950 px-5 py-3 text-white hover:bg-red-600 transition">Matricularme</button>
                    </form>
                </div>
            @empty
                <div class="rounded-3xl border border-slate-200 p-6 text-gray-500">No hay cursos disponibles para matrícula.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
