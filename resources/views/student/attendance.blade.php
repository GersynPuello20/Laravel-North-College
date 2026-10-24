@extends('layouts.app')

@section('title', 'Mi asistencia')

@section('content')
    <x-page-header title="Mi asistencia" subtitle="Todos tus registros. Si ves un error, habla con el tutor del grupo para que lo corrija." />

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach (\App\Models\SessionAttendance::STATUS_LABELS as $status => $label)
            <x-stat :label="$label" :value="$totals[$status]" />
        @endforeach
    </div>

    <div class="card">
        @if ($records->isEmpty())
            <x-empty-state icon="clipboard-document-check" title="Aún no tienes registros de asistencia" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Sesión</th><th>Grupo</th><th>Estado</th><th>Método</th></tr></thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td class="whitespace-nowrap tabular-nums">{{ $record->classSession->starts_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $record->classSession->course?->name }}</td>
                                <td>
                                    <x-attendance-badge :status="$record->status" />
                                    @if ($record->corrections->isNotEmpty())
                                        <x-badge tone="violet" class="ml-1" title="{{ $record->corrections->first()->reason }}">Corregido</x-badge>
                                    @endif
                                </td>
                                <td class="text-slate-500">{{ $record->methodLabel() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $records->links() }}
        @endif
    </div>
@endsection
