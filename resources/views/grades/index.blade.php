@extends('layouts.app')

@section('title', 'Notas')

@section('content')
    <x-page-header title="Notas" subtitle="Las registran los tutores desde sus grupos." />

    <div class="card">
        @if ($grades->isEmpty())
            <x-empty-state icon="document-text" title="Aún no hay notas registradas" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Estudiante</th><th>Asignatura</th><th>Grupo</th><th class="text-right">Nota</th><th>Registrada por</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        @foreach ($grades as $grade)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $grade->student?->name }}</td>
                                <td>{{ $grade->subject?->name ?? 'Sin asignatura' }}</td>
                                <td class="text-slate-500">{{ $grade->subject?->course?->name }}</td>
                                <td class="text-right font-semibold tabular-nums">{{ $grade->grade }}</td>
                                <td class="text-slate-500">{{ $grade->createdBy?->name ?? 'Sistema' }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.grades.destroy', $grade) }}" onsubmit="return confirm('¿Eliminar esta nota?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-ghost btn-sm" title="Eliminar"><x-icon name="trash" class="size-4" /></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $grades->links() }}
        @endif
    </div>
@endsection
