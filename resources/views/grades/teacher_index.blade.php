@extends('layouts.app')

@section('title', 'Notas')

@section('content')
    <x-page-header title="Notas de mis grupos">
        <x-slot:actions>
            <a href="{{ route('teacher.grades.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Nueva nota</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        @if ($grades->isEmpty())
            <x-empty-state icon="document-text" title="Aún no has registrado notas" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Estudiante</th><th>Asignatura</th><th class="text-right">Nota</th><th>Fecha</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        @foreach ($grades as $grade)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $grade->student?->name }}</td>
                                <td>{{ $grade->subject?->name ?? 'Sin asignatura' }} <span class="text-xs text-slate-500">· {{ $grade->subject?->course?->name }}</span></td>
                                <td class="text-right font-semibold tabular-nums">{{ $grade->grade }}</td>
                                <td class="tabular-nums text-slate-500">{{ $grade->created_at->format('d/m/Y') }}</td>
                                <td class="text-right"><a href="{{ route('teacher.grades.edit', $grade) }}" class="btn-ghost btn-sm"><x-icon name="pencil-square" class="size-4" /> Editar</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $grades->links() }}
        @endif
    </div>
@endsection
