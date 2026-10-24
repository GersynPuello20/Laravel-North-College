@extends('layouts.app')

@section('title', 'Mis matrículas')

@section('content')
    <x-page-header title="Mis matrículas">
        <x-slot:actions>
            <a href="{{ route('student.courses.available') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Matricularme en un curso</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        @if ($enrollments->isEmpty())
            <x-empty-state icon="clipboard-document-list" title="Aún no tienes matrículas">
                <x-slot:actions><a href="{{ route('student.courses.available') }}" class="btn-primary">Ver cursos disponibles</a></x-slot:actions>
            </x-empty-state>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($enrollments as $enrollment)
                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">{{ $enrollment->course?->name }}</p>
                            <p class="text-xs text-slate-500">Tutor: {{ $enrollment->course?->professor?->name ?? 'Sin asignar' }} · desde {{ $enrollment->enrollment_date?->format('d/m/Y') }}</p>
                        </div>
                        <form method="POST" action="{{ route('student.enrollments.destroy', $enrollment) }}" onsubmit="return confirm('¿Cancelar esta matrícula?')">
                            @csrf @method('DELETE')
                            <button class="btn-ghost btn-sm">Cancelar matrícula</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            {{ $enrollments->links() }}
        @endif
    </div>
@endsection
