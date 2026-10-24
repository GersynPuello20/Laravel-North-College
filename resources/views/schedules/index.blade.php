@extends('layouts.app')

@section('title', 'Horarios')

@section('content')
    <x-page-header title="Horarios" subtitle="A partir de los horarios se generan las sesiones de clase de cada día.">
        <x-slot:actions>
            <a href="{{ route('admin.schedules.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Nuevo horario</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        @if ($schedules->isEmpty())
            <x-empty-state icon="clock" title="Aún no hay horarios" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Grupo</th><th>Día</th><th>Hora</th><th>Aula</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $schedule->course?->name ?? 'Sin grupo' }}</td>
                                <td>
                                    {{ $schedule->dayLabel() }}
                                    @if (! $schedule->isoDay())
                                        <x-badge tone="amber" class="ml-1" title="No se reconoce el día: no se generarán sesiones">Revisar</x-badge>
                                    @endif
                                </td>
                                <td class="tabular-nums">{{ $schedule->start_time?->format('H:i') }}–{{ $schedule->end_time?->format('H:i') }}</td>
                                <td class="text-slate-600">{{ $schedule->classroom }}</td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn-ghost btn-sm"><x-icon name="pencil-square" class="size-4" /> Editar</a>
                                    <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="inline" onsubmit="return confirm('¿Eliminar este horario?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-ghost btn-sm" title="Eliminar"><x-icon name="trash" class="size-4" /></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $schedules->links() }}
        @endif
    </div>
@endsection
