@extends('layouts.app')

@section('title', 'Sesiones de clase')

@section('content')
    <x-page-header title="Sesiones de clase" subtitle="Abre la sesión al iniciar la clase, registra la asistencia y ciérrala al terminar.">
        <x-slot:actions>
            <form method="POST" action="{{ route('sessions.generate') }}" class="flex items-center gap-2">
                @csrf
                <label for="generate-date" class="sr-only">Fecha</label>
                <input id="generate-date" type="date" name="date" value="{{ today()->toDateString() }}" class="form-input w-40">
                <button class="btn-secondary" title="Crea las sesiones de esa fecha según los horarios de cada grupo">
                    <x-icon name="arrow-path" class="size-4" /> Generar desde horarios
                </button>
            </form>
            <a href="{{ route('sessions.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Nueva sesión</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-4">
            <div class="w-full sm:w-56">
                <label for="course_id" class="form-label">Grupo</label>
                <select id="course_id" name="course_id" class="form-input">
                    <option value="">Todos</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected(($filters['course_id'] ?? null) == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-40">
                <label for="status" class="form-label">Estado</label>
                <select id="status" name="status" class="form-input">
                    <option value="">Todos</option>
                    @foreach (\App\Models\ClassSession::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-44">
                <label for="date" class="form-label">Fecha</label>
                <input id="date" type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="form-input">
            </div>
            <div class="flex gap-2">
                <button class="btn-secondary"><x-icon name="funnel" class="size-4" /> Filtrar</button>
                @if (array_filter($filters))
                    <a href="{{ route('sessions.index') }}" class="btn-ghost">Limpiar</a>
                @endif
            </div>
        </form>

        @if (($filters['status'] ?? null) === 'open')
            <p class="flex items-center gap-2 border-b border-slate-100 bg-navy-50/50 px-5 py-3 text-sm text-navy-900">
                <x-icon name="camera" class="size-4" /> Para tomar asistencia con reconocimiento facial elige una sesión abierta.
            </p>
        @endif

        @if ($sessions->isEmpty())
            <x-empty-state icon="calendar-days" title="No hay sesiones con estos filtros">
                Crea una sesión manualmente o genérala a partir de los horarios de los grupos.
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha y hora</th>
                            <th>Grupo</th>
                            <th>Tutor</th>
                            <th>Estado</th>
                            <th class="text-right">Registros</th>
                            <th><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            <tr>
                                <td class="whitespace-nowrap tabular-nums">
                                    <span class="font-medium text-slate-900">{{ $session->starts_at->translatedFormat('D d/m/Y') }}</span>
                                    <span class="text-slate-500">{{ $session->starts_at->format('H:i') }}–{{ $session->ends_at->format('H:i') }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('sessions.show', $session) }}" class="font-medium text-slate-900 hover:text-navy-700">{{ $session->course?->name }}</a>
                                    @if ($session->topic)
                                        <p class="text-xs text-slate-500">{{ $session->topic }}</p>
                                    @endif
                                </td>
                                <td class="text-slate-500">{{ $session->course?->professor?->name ?? 'Sin asignar' }}</td>
                                <td><x-session-badge :session="$session" /></td>
                                <td class="text-right tabular-nums">
                                    {{ $session->attendances_count }}
                                    @if ($session->attendances_count)
                                        <span class="text-xs text-slate-500">({{ $session->present_count }} asistieron)</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    @if ($session->isOpen())
                                        <a href="{{ route('sessions.facial', $session) }}" class="btn-ghost btn-sm"><x-icon name="camera" class="size-4" /> Facial</a>
                                    @endif
                                    <a href="{{ route('sessions.show', $session) }}" class="btn-ghost btn-sm">Ver <x-icon name="chevron-right" class="size-4" /></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $sessions->links() }}
        @endif
    </div>
@endsection
