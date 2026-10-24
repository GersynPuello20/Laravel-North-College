@extends('layouts.app')

@section('title', 'Mis datos biométricos')

@section('content')
    <x-page-header title="Mis datos biométricos" subtitle="Qué se guarda, para qué se usa y cómo retirar tu autorización." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="card lg:col-span-1">
            <div class="card-header"><h2 class="card-title">Estado</h2></div>
            <div class="card-body space-y-3 text-sm">
                @if ($consent)
                    <p class="flex items-center gap-2 font-medium text-emerald-700"><x-icon name="shield-check" class="size-5" /> Autorización vigente</p>
                    <p class="text-slate-600">Otorgada el {{ $consent->granted_at->format('d/m/Y') }} por {{ $consent->granted_by_name }} ({{ $consent->relationshipLabel() }}).</p>
                    <p class="text-slate-600">{{ $template ? 'Tu rostro está enrolado.' : 'Tu rostro aún no ha sido enrolado.' }}</p>
                @else
                    <p class="font-medium text-slate-800">No tienes una autorización vigente.</p>
                    <p class="text-slate-600">Tu asistencia se registra de forma manual. Si quieres usar el reconocimiento facial, solicítalo a la coordinación académica.</p>
                @endif
            </div>

            @if ($consent)
                <form method="POST" action="{{ route('student.biometrics.revoke') }}" class="border-t border-slate-100 px-5 py-4"
                      onsubmit="return confirm('Se eliminarán tus datos biométricos y tu asistencia pasará a registrarse de forma manual. ¿Continuar?')">
                    @csrf
                    <label for="reason" class="form-label">Retirar mi autorización</label>
                    <textarea id="reason" name="reason" rows="2" maxlength="500" class="form-input" placeholder="Motivo (opcional)"></textarea>
                    @if ($isMinor)
                        <p class="form-hint">Eres menor de edad: tu representante legal también puede pedir el retiro ante la institución.</p>
                    @endif
                    <button class="btn-secondary mt-3 w-full">Retirar autorización y borrar mis datos</button>
                </form>
            @endif
        </section>

        <section class="card lg:col-span-2">
            <div class="card-header"><h2 class="card-title">Información del tratamiento</h2></div>
            <div class="card-body">@include('biometrics.partials.consent-text')</div>
        </section>

        @if ($history->isNotEmpty())
            <section class="card lg:col-span-3">
                <div class="card-header"><h2 class="card-title">Historial</h2></div>
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach ($history as $item)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                            <span>Autorización del {{ $item->granted_at->format('d/m/Y') }} (versión {{ $item->version }})</span>
                            <span class="flex items-center gap-2 text-xs text-slate-500">
                                @if (! $item->isActive()) Revocada {{ $item->revoked_at?->format('d/m/Y') }} @endif
                                <x-badge :tone="$item->isActive() ? 'green' : 'slate'">{{ $item->isActive() ? 'Vigente' : 'Revocada' }}</x-badge>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection
