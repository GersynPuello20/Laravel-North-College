@extends('layouts.app')

@section('title', 'Panel principal')

@section('content')
    <x-page-header :title="'Hola, '.\Illuminate\Support\Str::of(auth()->user()->name)->before(' ')" subtitle="Tu asistencia, tus clases y tus matrículas" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Mi asistencia" :value="$rate !== null ? $rate.' %' : '—'" icon="chart-bar"
                :hint="$rate !== null ? 'Presentes y tardes sobre el total' : 'Aún no tienes registros'" :href="route('student.attendance')" />
        <x-stat label="Presente" :value="$totals['present']" icon="check-circle" tone="green" />
        <x-stat label="Tarde" :value="$totals['late']" icon="clock" tone="amber" />
        <x-stat label="Ausente" :value="$totals['absent']" icon="x-circle" tone="red" :hint="$totals['excused'] ? $totals['excused'].' justificadas aparte' : null" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card xl:col-span-2">
            <div class="card-header">
                <h2 class="card-title">Registros recientes</h2>
                <a href="{{ route('student.attendance') }}" class="text-xs font-medium text-navy-700 hover:underline">Ver todo</a>
            </div>
            @if ($recent->isEmpty())
                <x-empty-state icon="clipboard-document-check" title="Todavía no hay registros de asistencia" />
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Fecha</th><th>Grupo</th><th>Estado</th><th>Método</th></tr></thead>
                        <tbody>
                            @foreach ($recent as $record)
                                <tr>
                                    <td class="whitespace-nowrap tabular-nums">{{ $record->classSession->starts_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $record->classSession->course?->name }}</td>
                                    <td><x-attendance-badge :status="$record->status" /></td>
                                    <td class="text-slate-500">{{ $record->methodLabel() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="space-y-6">
            <section class="card">
                <div class="card-header"><h2 class="card-title">Notas recientes</h2></div>
                @if ($grades->isEmpty())
                    <x-empty-state icon="document-text" title="Aún no tienes notas registradas" />
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($grades as $grade)
                            <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-slate-900">{{ $grade->subject?->name }}</span>
                                    <span class="block truncate text-xs text-slate-500">{{ $grade->subject?->course?->name }}</span>
                                </span>
                                <span class="font-semibold tabular-nums text-slate-900">{{ $grade->grade }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card">
                <div class="card-header"><h2 class="card-title">Próximas sesiones</h2></div>
                @if ($upcoming->isEmpty())
                    <x-empty-state icon="calendar-days" title="No hay sesiones programadas" />
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($upcoming as $session)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $session->course?->name }}</p>
                                    <p class="text-xs tabular-nums text-slate-500">{{ $session->starts_at->translatedFormat('D j M · H:i') }}</p>
                                </div>
                                <x-session-badge :session="$session" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card">
                <div class="card-header"><h2 class="card-title">Reconocimiento facial</h2></div>
                <div class="card-body text-sm text-slate-600">
                    @if ($consent && $hasTemplate)
                        <p class="flex items-center gap-2"><x-icon name="shield-check" class="size-5 text-emerald-600" /> Autorizado y enrolado.</p>
                    @elseif ($consent)
                        <p>Autorizaste el uso de tus datos biométricos, pero aún no se ha enrolado tu rostro.</p>
                    @else
                        <p>No has autorizado el reconocimiento facial. Tu asistencia se registra de forma manual.</p>
                    @endif
                    <a href="{{ route('student.biometrics') }}" class="mt-3 inline-flex text-xs font-medium text-navy-700 hover:underline">Ver mis datos y opciones</a>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Mis matrículas</h2>
                    <a href="{{ route('student.enrollments.index') }}" class="text-xs font-medium text-navy-700 hover:underline">Gestionar</a>
                </div>
                @if ($enrollments->isEmpty())
                    <x-empty-state icon="book-open" title="Aún no estás matriculado">
                        <x-slot:actions><a href="{{ route('student.courses.available') }}" class="btn-primary btn-sm">Ver cursos disponibles</a></x-slot:actions>
                    </x-empty-state>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($enrollments as $enrollment)
                            <li class="px-5 py-3 text-sm">
                                <p class="font-medium text-slate-900">{{ $enrollment->course?->name }}</p>
                                <p class="text-xs text-slate-500">Tutor: {{ $enrollment->course?->professor?->name ?? 'Sin asignar' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection
