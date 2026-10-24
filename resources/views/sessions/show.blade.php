@extends('layouts.app')

@section('title', $session->course?->name.' · '.$session->starts_at->format('d/m'))

@section('content')
    @php $statuses = \App\Models\SessionAttendance::STATUS_LABELS; @endphp

    <x-page-header
        :title="$session->course?->name"
        :subtitle="$session->starts_at->translatedFormat('l j \d\e F').' · '.$session->starts_at->format('H:i').'–'.$session->ends_at->format('H:i').($session->topic ? ' · '.$session->topic : '')"
        :back="route('sessions.index')">
        <x-slot:actions>
            <x-session-badge :session="$session" class="mr-1" />
            @can('manage', $session)
                @if ($session->isScheduled())
                    <form method="POST" action="{{ route('sessions.open', $session) }}">
                        @csrf
                        <button class="btn-primary"><x-icon name="play" class="size-4" /> Abrir sesión</button>
                    </form>
                @elseif ($session->isOpen())
                    <a href="{{ route('sessions.facial', $session) }}" class="btn-secondary"><x-icon name="camera" class="size-4" /> Reconocimiento facial</a>
                    <form method="POST" action="{{ route('sessions.close', $session) }}"
                          onsubmit="return confirm('Al cerrar, los {{ $pending }} estudiantes sin registro quedarán como ausentes. ¿Cerrar la sesión?')">
                        @csrf
                        <button class="btn-danger"><x-icon name="stop" class="size-4" /> Cerrar sesión</button>
                    </form>
                @endif
            @endcan
            @can('delete', $session)
                <form method="POST" action="{{ route('sessions.destroy', $session) }}" onsubmit="return confirm('¿Eliminar esta sesión? No tiene registros.')">
                    @csrf @method('DELETE')
                    <button class="btn-ghost" title="Eliminar sesión"><x-icon name="trash" class="size-4" /></button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="card px-4 py-3">
            <p class="text-xs text-slate-500">Matriculados</p>
            <p class="text-xl font-semibold tabular-nums">{{ $roster->count() }}</p>
        </div>
        <div class="card px-4 py-3">
            <p class="text-xs text-slate-500">Sin registro</p>
            <p class="text-xl font-semibold tabular-nums">{{ $pending }}</p>
        </div>
        @foreach ($statuses as $status => $label)
            <div class="card px-4 py-3">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <p class="text-xl font-semibold tabular-nums">{{ $summary[$status] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-500">
        <span>Tarde después de las <strong class="text-slate-700">{{ $session->lateAfter()->format('H:i') }}</strong></span>
        @if ($session->opened_at)
            <span>Abierta {{ $session->opened_at->format('H:i') }} por {{ $session->openedBy?->name }}</span>
        @endif
        @if ($session->closed_at)
            <span>Cerrada {{ $session->closed_at->format('H:i') }} por {{ $session->closedBy?->name }}</span>
        @endif
    </div>

    @if ($session->isScheduled())
        <div class="mb-4 flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
            <x-icon name="information-circle" class="size-5 shrink-0 text-slate-400" />
            La sesión está programada. Ábrela para empezar a registrar asistencia.
        </div>
    @elseif ($session->isClosed())
        <div class="mb-4 flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
            <x-icon name="lock-closed" class="size-5 shrink-0 text-slate-400" />
            La sesión está cerrada. Los cambios solo se pueden hacer como corrección, con un motivo que queda en el historial.
        </div>
    @endif

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">Lista de asistencia</h2>
            @if ($session->isOpen() && $pending > 0)
                @can('manage', $session)
                    <div class="flex items-center gap-2">
                        <span class="hidden text-xs text-slate-500 sm:inline">Elige un estado por estudiante y guarda.</span>
                        <button form="bulk-form" class="btn-primary btn-sm"><x-icon name="check" class="size-4" /> Guardar registros</button>
                    </div>
                @endcan
            @endif
        </div>

        @can('manage', $session)
            <form id="bulk-form" method="POST" action="{{ route('sessions.attendances.store', $session) }}">@csrf</form>
        @endcan

        @if ($roster->isEmpty())
            <x-empty-state icon="academic-cap" title="Este grupo no tiene estudiantes con matrícula activa">
                Matricula estudiantes en el grupo para poder registrar su asistencia.
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th class="text-right"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roster as $student)
                            @php $record = $records->get($student->id); @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">{{ $student->initials() }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-slate-900">{{ $student->name }}</p>
                                            <p class="flex items-center gap-1 text-xs text-slate-500">
                                                @if ($student->faceTemplate)
                                                    <x-icon name="finger-print" class="size-3.5 text-emerald-600" /> Enrolado
                                                @else
                                                    Registro manual
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($record)
                                        <x-attendance-badge :status="$record->status" />
                                        @if ($record->corrections->isNotEmpty())
                                            <x-badge tone="violet" class="ml-1">Corregido</x-badge>
                                        @endif
                                    @elseif ($session->isOpen())
                                        @can('manage', $session)
                                            <label class="sr-only" for="mark-{{ $student->id }}">Estado de {{ $student->name }}</label>
                                            <select id="mark-{{ $student->id }}" name="marks[{{ $student->id }}]" form="bulk-form" class="form-input w-48 py-1.5">
                                                <option value="">Sin registrar</option>
                                                <option value="auto">Presente (según la hora)</option>
                                                @foreach ($statuses as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @endcan
                                    @else
                                        <span class="text-xs text-slate-400">Sin registro</span>
                                    @endif
                                </td>
                                <td class="text-xs text-slate-500">
                                    @if ($record)
                                        <span class="tabular-nums">{{ $record->recorded_at->format('H:i') }}</span> · {{ $record->methodLabel() }}
                                        @if ($record->match_score !== null)
                                            · similitud {{ number_format($record->match_score, 2) }}
                                        @endif
                                        <br>{{ $record->recordedBy?->name }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($record)
                                        @can('update', $record)
                                            @include('sessions.partials.correction', ['record' => $record])
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($orphans->isNotEmpty())
        <section class="card mt-6">
            <div class="card-header">
                <h2 class="card-title">Registros de estudiantes sin matrícula activa</h2>
            </div>
            <ul class="divide-y divide-slate-100">
                @foreach ($orphans as $record)
                    <li class="flex items-center justify-between px-5 py-3 text-sm">
                        <span>{{ $record->student?->name }}</span>
                        <x-attendance-badge :status="$record->status" />
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
