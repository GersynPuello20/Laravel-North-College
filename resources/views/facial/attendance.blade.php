@extends('layouts.app')

@section('title', 'Reconocimiento facial')

@section('content')
    <x-page-header
        title="Asistencia por reconocimiento facial"
        :subtitle="$session->course?->name.' · '.$session->starts_at->translatedFormat('l j \d\e F').' · '.$session->starts_at->format('H:i').'–'.$session->ends_at->format('H:i')"
        :back="route('sessions.show', $session)">
        <x-slot:actions>
            <x-session-badge :session="$session" />
            <a href="{{ route('sessions.show', $session) }}" class="btn-secondary"><x-icon name="clipboard-document-check" class="size-4" /> Registro manual</a>
        </x-slot:actions>
    </x-page-header>

    @if (! $session->isOpen())
        <div class="card">
            <x-empty-state icon="lock-closed" :title="$session->isClosed() ? 'La sesión está cerrada' : 'La sesión aún no está abierta'">
                {{ $session->isClosed() ? 'Ya no se puede registrar asistencia. Usa una corrección si hace falta.' : 'Ábrela desde la hoja de asistencia para empezar.' }}
                <x-slot:actions>
                    <a href="{{ route('sessions.show', $session) }}" class="btn-primary">Ir a la hoja de asistencia</a>
                </x-slot:actions>
            </x-empty-state>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5"
             x-data="facialCapture({
                mode: 'attendance',
                challengeUrl: @js(route('sessions.facial.challenge', $session)),
                detectUrl: @js(route('sessions.facial.detect', $session)),
                submitUrl: @js(route('sessions.facial.recognize', $session)),
             })">
            <div class="space-y-4 lg:col-span-3">
                @include('facial.partials.camera', ['action' => 'Verificar asistencia'])

                {{-- Resultado --}}
                <template x-if="result">
                    <div class="flex items-start gap-3 rounded-lg border px-4 py-3"
                         :class="result.code === 'matched' ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                                : (result.code === 'already_registered' ? 'border-sky-200 bg-sky-50 text-sky-900'
                                : 'border-amber-200 bg-amber-50 text-amber-900')">
                        <span class="mt-0.5 shrink-0" x-show="result.ok"><x-icon name="check-circle" /></span>
                        <span class="mt-0.5 shrink-0" x-show="!result.ok"><x-icon name="exclamation-triangle" /></span>
                        <div class="min-w-0 flex-1 text-sm">
                            <p class="font-semibold" x-text="result.message"></p>
                            <p class="mt-0.5 text-xs opacity-80" x-show="result.status">
                                Estado: <span x-text="result.status"></span><span x-show="result.recorded_at"> · <span x-text="result.recorded_at"></span></span>
                            </p>
                            <p class="mt-0.5 text-xs opacity-80" x-show="result.score !== null && result.score !== undefined">
                                Similitud: <span x-text="Number(result.score).toFixed(3)"></span>
                                (umbral {{ number_format(config('biometrics.match_threshold'), 3) }})
                            </p>
                        </div>
                    </div>
                </template>

                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-xs text-slate-500">
                    <p class="font-medium text-slate-700">Cómo funciona</p>
                    <ol class="mt-1 list-decimal space-y-0.5 pl-4">
                        <li>Una sola persona frente a la cámara, con buena luz.</li>
                        <li>Sigue las instrucciones: de frente, girar la cabeza hacia el lado indicado y volver.</li>
                        <li>Solo se compara contra los estudiantes enrolados de este grupo. Si la coincidencia no es clara, no se registra nada.</li>
                    </ol>
                    <p class="mt-2">La prueba de presencia reduce el uso de fotos, pero no es infalible: el tutor debe supervisar la toma.</p>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Sesión</h2></div>
                    <dl class="card-body grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500">Grupo</dt>
                            <dd class="font-medium text-slate-900">{{ $session->course?->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Tutor</dt>
                            <dd class="font-medium text-slate-900">{{ $session->course?->professor?->name ?? 'Sin asignar' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Horario</dt>
                            <dd class="font-medium tabular-nums text-slate-900">{{ $session->starts_at->format('H:i') }}–{{ $session->ends_at->format('H:i') }}{{ $session->schedule ? ' · '.$session->schedule->classroom : '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Tarde desde</dt>
                            <dd class="font-medium tabular-nums text-slate-900">{{ $session->lateAfter()->format('H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Registrados</dt>
                            <dd class="font-medium tabular-nums text-slate-900">{{ $registeredCount }} / {{ $rosterCount }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Con enrolamiento facial</dt>
                            <dd class="font-medium tabular-nums text-slate-900">{{ $enrolledCount }} / {{ $rosterCount }}</dd>
                        </div>
                    </dl>
                    @if ($enrolledCount === 0)
                        <p class="border-t border-slate-100 px-5 py-3 text-xs text-amber-700">Ningún estudiante de este grupo tiene enrolamiento vigente: usa el registro manual.</p>
                    @endif
                </section>

                <section class="card">
                    <div class="card-header"><h2 class="card-title">Intentos en esta pantalla</h2></div>
                    <template x-if="history.length === 0">
                        <p class="px-5 py-6 text-center text-sm text-slate-500">Aún no hay intentos.</p>
                    </template>
                    <ul class="divide-y divide-slate-100">
                        <template x-for="(item, index) in history" :key="index">
                            <li class="flex items-start gap-3 px-5 py-3 text-sm">
                                <span class="mt-1.5 size-2 shrink-0 rounded-full" :class="item.code === 'matched' ? 'bg-emerald-500' : (item.ok ? 'bg-sky-500' : 'bg-amber-500')"></span>
                                <div class="min-w-0">
                                    <p class="truncate text-slate-900" x-text="item.message"></p>
                                    <p class="text-xs text-slate-500" x-text="item.at"></p>
                                </div>
                            </li>
                        </template>
                    </ul>
                </section>

                <section class="card">
                    <div class="card-header"><h2 class="card-title">Últimos registros de la sesión</h2></div>
                    @if ($recent->isEmpty())
                        <p class="px-5 py-6 text-center text-sm text-slate-500">Sin registros todavía.</p>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($recent as $record)
                                <li class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm">
                                    <span class="truncate">{{ $record->student?->name }}</span>
                                    <span class="flex shrink-0 items-center gap-2">
                                        <span class="text-xs text-slate-500">{{ $record->methodLabel() }}</span>
                                        <x-attendance-badge :status="$record->status" />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="border-t border-slate-100 px-5 py-2 text-xs text-slate-400">Recarga la página para actualizar esta lista.</p>
                    @endif
                </section>
            </div>
        </div>
    @endif
@endsection
