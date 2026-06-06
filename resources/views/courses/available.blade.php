@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-blue-950">Cursos disponibles</h1>
        <p class="text-gray-500 mt-2">Selecciona los cursos en los que deseas matricularte.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($availableCourses as $course)
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h2 class="text-2xl font-semibold text-blue-950">{{ $course->name }}</h2>
                <p class="mt-2 text-gray-600">{{ $course->description }}</p>
                <p class="mt-3 text-sm text-gray-500">Profesor: {{ $course->professor?->name ?? 'Sin profesor' }}</p>
                <form action="{{ route('student.enrollments.store') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <button type="submit" class="rounded-xl bg-blue-950 px-5 py-3 text-white font-semibold hover:bg-red-600 transition">Matricularme</button>
                </form>
            </div>
        @empty
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <p class="text-gray-500">No hay cursos disponibles para matricular.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $availableCourses->links() }}
    </div>
</div>
@endsection
