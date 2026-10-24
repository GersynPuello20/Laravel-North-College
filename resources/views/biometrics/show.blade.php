@extends('layouts.app')

@section('title', 'Enrolamiento facial · '.$student->name)

@section('content')
    @php
        $isMinor = $profile?->isMinor();
        $step = ! $profile?->birth_date ? 1 : (! $consent ? 2 : 3);
    @endphp

    <x-page-header :title="'Enrolamiento facial · '.$student->name" subtitle="Autorización y captura del rostro para la asistencia por reconocimiento facial." :back="route('students.show', $student)">
        <x-slot:actions>
            @if ($template)
                <x-badge tone="green"><x-icon name="finger-print" class="size-3.5" /> Enrolado</x-badge>
            @elseif ($consent)
                <x-badge tone="amber">Autorizado, sin enrolar</x-badge>
            @else
                <x-badge>Sin autorización</x-badge>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (! $student->isActive())
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <x-icon name="exclamation-triangle" class="size-5" /> La cuenta del estudiante no está activa: no se puede enrolar.
        </div>
    @endif

    <ol class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach ([1 => 'Datos del estudiante', 2 => 'Autorización', 3 => 'Captura del rostro'] as $n => $label)
            <li @class([
                'flex items-center gap-3 rounded-lg border px-4 py-3 text-sm',
                'border-navy-300 bg-navy-50 text-navy-900' => $step === $n,
                'border-emerald-200 bg-emerald-50 text-emerald-800' => $step > $n || ($n === 3 && $template),
                'border-slate-200 bg-white text-slate-500' => $step < $n && ! ($n === 3 && $template),
            ])>
                <span class="flex size-6 items-center justify-center rounded-full bg-white text-xs font-semibold ring-1 ring-inset ring-current">{{ $n }}</span>
                {{ $label }}
            </li>
        @endforeach
    </ol>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-2">
            {{-- Paso 1 --}}
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">1. Datos del estudiante</h2>
                    <a href="{{ route('students.edit', $student) }}" class="text-xs font-medium text-navy-700 hover:underline">Editar</a>
                </div>
                <dl class="card-body grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-slate-500">Documento</dt><dd>{{ $profile?->document_number ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Código</dt><dd>{{ $profile?->enrollment_number ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Nacimiento</dt><dd>{{ $profile?->birth_date?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Edad</dt>
                        <dd>
                            @if ($isMinor === null) <span class="text-amber-700">Sin fecha</span>
                            @else {{ $profile->age() }} años @if ($isMinor) <x-badge tone="amber" class="ml-1">Menor</x-badge> @endif
                            @endif
                        </dd>
                    </div>
                    @if ($isMinor)
                        <div class="col-span-2"><dt class="text-xs text-slate-500">Acudiente registrado</dt><dd>{{ $profile->guardian_name ?? '—' }} {{ $profile->guardian_document ? '· '.$profile->guardian_document : '' }}</dd></div>
                    @endif
                </dl>
                @if ($step === 1)
                    <p class="border-t border-slate-100 px-5 py-3 text-xs text-amber-700">Registra la fecha de nacimiento: define si la autorización la da el estudiante o su representante legal.</p>
                @endif
            </section>

            {{-- Paso 2 --}}
            <section class="card">
                <div class="card-header"><h2 class="card-title">2. Autorización de tratamiento</h2></div>

                @if ($consent)
                    <div class="card-body space-y-3 text-sm">
                        <p class="flex items-center gap-2 font-medium text-emerald-700"><x-icon name="shield-check" class="size-5" /> Autorización vigente</p>
                        <dl class="grid grid-cols-2 gap-3">
                            <div><dt class="text-xs text-slate-500">Otorgada por</dt><dd>{{ $consent->granted_by_name }}</dd></div>
                            <div><dt class="text-xs text-slate-500">Calidad</dt><dd>{{ $consent->relationshipLabel() }}</dd></div>
                            <div><dt class="text-xs text-slate-500">Fecha</dt><dd>{{ $consent->granted_at->format('d/m/Y H:i') }}</dd></div>
                            <div><dt class="text-xs text-slate-500">Versión</dt><dd>{{ $consent->version }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-slate-500">Registrada por</dt><dd>{{ $consent->recordedBy?->name }}</dd></div>
                        </dl>
                        @if ($consent->version !== config('biometrics.consent_version'))
                            <p class="rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">El texto de la autorización cambió (versión actual {{ config('biometrics.consent_version') }}). Conviene registrar una autorización nueva.</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('students.biometrics.revoke', $student) }}" class="border-t border-slate-100 px-5 py-4"
                          onsubmit="return confirm('Se revocará la autorización y se eliminará el vector biométrico. ¿Continuar?')">
                        @csrf
                        <label for="revoke-reason" class="form-label">Revocar autorización</label>
                        <div class="flex gap-2">
                            <input id="revoke-reason" name="reason" required minlength="5" maxlength="500" class="form-input" placeholder="Motivo (p. ej. solicitud del titular)">
                            <button class="btn-secondary shrink-0">Revocar</button>
                        </div>
                    </form>
                @elseif ($step >= 2)
                    <form method="POST" action="{{ route('students.biometrics.consent', $student) }}" class="card-body space-y-4">
                        @csrf
                        <details class="rounded-lg border border-slate-200 bg-slate-50 p-4" open>
                            <summary class="cursor-pointer text-sm font-medium text-slate-800">Información que se debe dar al titular</summary>
                            <div class="mt-3">@include('biometrics.partials.consent-text')</div>
                        </details>

                        <x-field :label="$isMinor ? 'Nombre del representante legal' : 'Nombre del estudiante'" name="granted_by_name" required>
                            <input id="granted_by_name" name="granted_by_name" required maxlength="255" class="form-input"
                                   value="{{ old('granted_by_name', $isMinor ? $profile?->guardian_name : $student->name) }}">
                        </x-field>
                        <x-field label="Documento de identidad" name="granted_by_document" required>
                            <input id="granted_by_document" name="granted_by_document" required maxlength="30" class="form-input"
                                   value="{{ old('granted_by_document', $isMinor ? $profile?->guardian_document : $profile?->document_number) }}">
                        </x-field>
                        <x-field label="Calidad en que autoriza" name="granted_by_relationship" required>
                            <select id="granted_by_relationship" name="granted_by_relationship" class="form-input" required>
                                @foreach (\App\Models\BiometricConsent::RELATIONSHIPS as $value => $label)
                                    @continue($isMinor && $value === 'self')
                                    @continue(! $isMinor && $value !== 'self')
                                    <option value="{{ $value }}" @selected(old('granted_by_relationship') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-field>

                        <div class="space-y-3 rounded-lg border border-slate-200 p-4 text-sm">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="informed" value="1" class="form-checkbox mt-0.5" required>
                                <span>Se informó la finalidad, qué datos se guardan, los derechos del titular y el canal para ejercerlos.</span>
                            </label>
                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="voluntary" value="1" class="form-checkbox mt-0.5" required>
                                <span>El titular sabe que no está obligado a autorizar y que puede registrar su asistencia de forma manual.</span>
                            </label>
                            @if ($isMinor)
                                <label class="flex items-start gap-3">
                                    <input type="checkbox" name="minor_was_heard" value="1" class="form-checkbox mt-0.5" required>
                                    <span>Se escuchó la opinión del menor y está de acuerdo con el tratamiento.</span>
                                </label>
                            @endif
                        </div>

                        <p class="text-xs text-slate-500">La autorización debe quedar también soportada por escrito (firma o medio verificable) según el procedimiento de la institución.</p>
                        <button class="btn-primary w-full">Registrar autorización</button>
                    </form>
                @else
                    <p class="card-body text-sm text-slate-500">Completa primero los datos del estudiante.</p>
                @endif
            </section>

            @if ($student->biometricConsents->isNotEmpty())
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Historial de autorizaciones</h2></div>
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($student->biometricConsents as $item)
                            <li class="px-5 py-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span>{{ $item->granted_at->format('d/m/Y') }} · {{ $item->granted_by_name }}</span>
                                    <x-badge :tone="$item->isActive() ? 'green' : 'slate'">{{ $item->isActive() ? 'Vigente' : 'Revocada' }}</x-badge>
                                </div>
                                @if (! $item->isActive())
                                    <p class="mt-1 text-xs text-slate-500">Revocada {{ $item->revoked_at?->format('d/m/Y H:i') }} por {{ $item->revokedBy?->name }} · {{ $item->revocation_reason }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Paso 3 --}}
        <section class="lg:col-span-3">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">3. Captura del rostro</h2>
                    @if ($template)
                        <span class="text-xs text-slate-500">Enrolado {{ $template->updated_at->format('d/m/Y') }} por {{ $template->enrolledBy?->name }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($consent && $student->isActive())
                        <div x-data="facialCapture({
                                mode: 'enroll',
                                challengeUrl: @js(route('students.biometrics.challenge', $student)),
                                detectUrl: @js(route('students.biometrics.detect', $student)),
                                submitUrl: @js(route('students.biometrics.enroll', $student)),
                             })" class="space-y-4">
                            @include('facial.partials.camera', ['action' => $template ? 'Volver a enrolar' : 'Enrolar rostro'])

                            <template x-if="result">
                                <div class="flex items-start gap-3 rounded-lg border px-4 py-3 text-sm"
                                     :class="result.ok ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-amber-200 bg-amber-50 text-amber-900'">
                                    <span x-show="result.ok"><x-icon name="check-circle" /></span>
                                    <span x-show="!result.ok"><x-icon name="exclamation-triangle" /></span>
                                    <div>
                                        <p class="font-semibold" x-text="result.message"></p>
                                        <p class="text-xs opacity-80" x-show="!result.ok">Puedes repetir la captura.</p>
                                    </div>
                                </div>
                            </template>

                            <p class="text-xs text-slate-500">Se toman 8 fotogramas en unos 4 segundos: de frente, girando la cabeza hacia el lado indicado y de nuevo de frente. Solo se guarda el vector biométrico cifrado; las imágenes no se almacenan.</p>
                        </div>
                    @else
                        <x-empty-state icon="lock-closed" title="La captura se habilita con una autorización vigente">
                            Sin autorización no se procesa ninguna imagen del estudiante.
                        </x-empty-state>
                    @endif
                </div>

                @if ($template)
                    <form method="POST" action="{{ route('students.biometrics.destroy', $student) }}" class="border-t border-slate-100 px-5 py-4"
                          onsubmit="return confirm('¿Eliminar el vector biométrico? El estudiante pasará a registro manual.')">
                        @csrf @method('DELETE')
                        <label for="delete-reason" class="form-label">Eliminar datos biométricos (la autorización se mantiene)</label>
                        <div class="flex gap-2">
                            <input id="delete-reason" name="reason" required minlength="5" maxlength="500" class="form-input" placeholder="Motivo">
                            <button class="btn-secondary shrink-0"><x-icon name="trash" class="size-4" /> Eliminar</button>
                        </div>
                    </form>
                @endif
            </div>

            @if ($attempts->isNotEmpty())
                <div class="card mt-6">
                    <div class="card-header"><h2 class="card-title">Últimos intentos de enrolamiento</h2></div>
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($attempts as $attempt)
                            <li class="flex items-center justify-between px-5 py-2.5">
                                <span>{{ $attempt->resultLabel() }}</span>
                                <span class="text-xs text-slate-500">{{ $attempt->created_at->format('d/m/Y H:i') }} · {{ $attempt->performedBy?->name }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    </div>
@endsection
