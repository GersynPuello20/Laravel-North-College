@extends('layouts.app')

@section('title', 'Panel principal')

@section('content')
    @php
        $user = auth()->user();
        $todayTotal = $today->sum();
        $attendedToday = $today['present'] + $today['late'];
    @endphp

    <x-page-header
        :title="'Hola, '.\Illuminate\Support\Str::of($user->name)->before(' ')"
        :subtitle="($user->isTeacher() ? 'Asistencia de tus grupos' : 'Asistencia de la institución').' · '.now()->translatedFormat('l j \d\e F')">
        <x-slot:actions>
            <a href="{{ route('sessions.index', ['date' => today()->toDateString()]) }}" class="btn-secondary">
                <x-icon name="calendar-days" class="size-4" /> Sesiones de hoy
            </a>
            <a href="{{ route('sessions.create') }}" class="btn-primary">
                <x-icon name="plus" class="size-4" /> Nueva sesión
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Estudiantes activos" :value="number_format($activeStudents)" icon="academic-cap"
                :hint="$user->isTeacher() ? 'Con matrícula activa en tus grupos' : 'Con cuenta activa'" />
        <x-stat label="Asistieron hoy" :value="$todayTotal ? $attendedToday : '—'" icon="check-circle" tone="green"
                :hint="$todayTotal ? $today['late'].' de ellos llegaron tarde' : 'Aún no hay registros hoy'" />
        <x-stat label="Ausencias hoy" :value="$todayTotal ? $today['absent'] + $today['excused'] : '—'" icon="x-circle" tone="red"
                :hint="$todayTotal ? $today['excused'].' justificadas' : 'Se calculan al cerrar cada sesión'" />
        <x-stat label="Asistencia (30 días)" :value="$rate !== null ? $rate.' %' : '—'" icon="chart-bar" tone="navy"
                :hint="$rate !== null ? 'Presentes y tardes en sesiones cerradas' : 'Sin sesiones cerradas en el periodo'"
                :href="route('reports.attendance')" />
    </div>

    @if ($unclosed > 0 || $needsReview > 0 || $facialFailures > 0)
        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
            @if ($unclosed > 0)
                <a href="{{ route('sessions.index') }}" class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 hover:border-amber-300">
                    <x-icon name="exclamation-triangle" class="size-5 shrink-0" />
                    <span><strong>{{ $unclosed }}</strong> {{ $unclosed === 1 ? 'sesión terminó y no se ha cerrado' : 'sesiones terminaron y no se han cerrado' }}. Las ausencias se registran al cerrar.</span>
                </a>
            @endif
            @if ($needsReview > 0)
                <a href="{{ route('reports.attendance', ['needs_review' => 1]) }}" class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 hover:border-amber-300">
                    <x-icon name="eye" class="size-5 shrink-0" />
                    <span><strong>{{ $needsReview }}</strong> registros pendientes de revisión.</span>
                </a>
            @endif
            @if ($facialFailures > 0)
                <a href="{{ route('reports.attendance', ['from' => today()->subDays(6)->toDateString()]) }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 hover:border-slate-300">
                    <x-icon name="camera" class="size-5 shrink-0 text-slate-400" />
                    <span><strong>{{ $facialFailures }}</strong> intentos de reconocimiento sin registro en los últimos 7 días.</span>
                </a>
            @endif
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card xl:col-span-2">
            <div class="card-header">
                <h2 class="card-title">Sesiones de hoy</h2>
                <span class="text-xs text-slate-500">{{ $todaySessions->count() }} programadas</span>
            </div>

            @if ($todaySessions->isEmpty())
                <x-empty-state icon="calendar-days" title="No hay sesiones programadas para hoy">
                    Crea una sesión o genérala desde los horarios de los grupos.
                    <x-slot:actions>
                        <form method="POST" action="{{ route('sessions.generate') }}">
                            @csrf
                            <input type="hidden" name="date" value="{{ today()->toDateString() }}">
                            <button class="btn-secondary"><x-icon name="arrow-path" class="size-4" /> Generar desde horarios</button>
                        </form>
                        <a href="{{ route('sessions.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Nueva sesión</a>
                    </x-slot:actions>
                </x-empty-state>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($todaySessions as $session)
                        @php
                            $roster = $session->roster_count;
                            $progress = $roster > 0 ? min(100, round($session->attendances_count * 100 / $roster)) : 0;
                        @endphp
                        <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center">
                            <div class="w-24 shrink-0 text-sm tabular-nums text-slate-500">
                                {{ $session->starts_at->format('H:i') }}–{{ $session->ends_at->format('H:i') }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('sessions.show', $session) }}" class="truncate font-medium text-slate-900 hover:text-navy-700">{{ $session->course?->name }}</a>
                                    <x-session-badge :session="$session" />
                                </div>
                                <div class="mt-2 flex items-center gap-3">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="h-full rounded-full bg-navy-700" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <span class="shrink-0 text-xs tabular-nums text-slate-500">{{ $session->attendances_count }}/{{ $roster }} registrados</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                @if ($session->isOpen())
                                    <a href="{{ route('sessions.facial', $session) }}" class="btn-secondary btn-sm"><x-icon name="camera" class="size-4" /> Facial</a>
                                @endif
                                <a href="{{ route('sessions.show', $session) }}" class="btn-ghost btn-sm">Abrir <x-icon name="chevron-right" class="size-4" /></a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="space-y-6">
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Enrolamiento facial</h2>
                    @if ($user->isStaff())
                        <a href="{{ route('students.index', ['biometric' => 'none']) }}" class="text-xs font-medium text-navy-700 hover:underline">Ver pendientes</a>
                    @endif
                </div>
                <div class="card-body">
                    @php $coveragePct = $coverage['students'] > 0 ? round($coverage['enrolled'] * 100 / $coverage['students']) : 0; @endphp
                    <p class="text-2xl font-semibold tabular-nums text-slate-900">{{ $coverage['enrolled'] }} <span class="text-sm font-normal text-slate-500">de {{ $coverage['students'] }} estudiantes</span></p>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-accent-500" style="width: {{ $coveragePct }}%"></div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">El enrolamiento es voluntario. Quien no lo autorice registra su asistencia de forma manual.</p>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Actividad reciente</h2>
                </div>
                @if ($activity->isEmpty())
                    <x-empty-state icon="clipboard-document-check" title="Sin actividad todavía">
                        Aquí aparecerán los registros y correcciones de asistencia.
                    </x-empty-state>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($activity as $item)
                            <li class="flex gap-3 px-5 py-3">
                                <span @class([
                                    'mt-1.5 size-2 shrink-0 rounded-full',
                                    'bg-emerald-500' => $item['tone'] === 'present',
                                    'bg-amber-500' => $item['tone'] === 'late',
                                    'bg-accent-500' => $item['tone'] === 'absent',
                                    'bg-sky-500' => $item['tone'] === 'excused',
                                    'bg-violet-500' => $item['tone'] === 'correction',
                                ])></span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $item['title'] }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $item['detail'] }} · {{ $item['at']->diffForHumans() }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection
