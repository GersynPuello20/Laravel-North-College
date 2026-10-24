@extends('layouts.app')

@section('title', 'Asignaturas')

@section('content')
    <x-page-header title="Asignaturas" subtitle="Asignaturas de cada grupo; se usan para registrar notas.">
        <x-slot:actions>
            <a href="{{ route('admin.subjects.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Nueva asignatura</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        @if ($subjects->isEmpty())
            <x-empty-state icon="book-open" title="Aún no hay asignaturas" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Asignatura</th><th>Grupo</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        @foreach ($subjects as $subject)
                            <tr>
                                <td>
                                    <p class="font-medium text-slate-900">{{ $subject->name }}</p>
                                    @if ($subject->description)
                                        <p class="max-w-md truncate text-xs text-slate-500">{{ $subject->description }}</p>
                                    @endif
                                </td>
                                <td class="text-slate-600">{{ $subject->course?->name ?? 'Sin grupo' }}</td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-ghost btn-sm"><x-icon name="pencil-square" class="size-4" /> Editar</a>
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline" onsubmit="return confirm('¿Eliminar la asignatura y sus notas?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-ghost btn-sm" title="Eliminar"><x-icon name="trash" class="size-4" /></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $subjects->links() }}
        @endif
    </div>
@endsection
