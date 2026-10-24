@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-blue-950">Mis matrículas</h1>
        <p class="text-gray-500 mt-2">Revisa tus cursos matriculados y accede a los detalles.</p>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm">
        @forelse($enrollments as $enrollment)
            <div class="border-b border-slate-200 py-4 last:border-none">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-blue-950">{{ $enrollment->course?->name }}</h2>
                        <p class="text-gray-500">Profesor: {{ $enrollment->course?->professor?->name ?? 'Sin profesor' }}</p>
                        <p class="text-sm text-gray-400">Matriculado el {{ $enrollment->enrollment_date->format('d/m/Y') }}</p>
                    </div>
                    <form action="{{ route('student.enrollments.destroy', $enrollment) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl bg-red-500 px-4 py-2 text-white hover:bg-red-600 transition">Cancelar</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500">Aún no estás matriculado en ningún curso.</p>
        @endforelse
    </div>

    <div>{{ $enrollments->links() }}</div>
</div>
@endsection
