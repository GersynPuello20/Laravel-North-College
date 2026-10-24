@extends('layouts.app')

@section('title', 'Configuración')

@section('content')
    <x-page-header title="Configuración" subtitle="Valores de solo lectura. Se cambian en el archivo .env del servidor, para que todo cambio quede controlado en el despliegue." />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">Motor de reconocimiento facial</h2>
                @if ($health)
                    <x-badge tone="green">Conectado</x-badge>
                @else
                    <x-badge tone="red">No disponible</x-badge>
                @endif
            </div>
            <dl class="card-body grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">URL del servicio</dt><dd class="font-mono text-xs">{{ $config['service_url'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">Token</dt><dd>{{ $config['service_token'] ? 'Configurado' : 'Falta FACE_SERVICE_TOKEN' }}</dd></div>
                @if ($health)
                    <div><dt class="text-xs text-slate-500">Motor</dt><dd class="font-mono text-xs">{{ $health['engine'] ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">OpenCV</dt><dd>{{ $health['opencv'] ?? '—' }}</dd></div>
                @else
                    <div class="sm:col-span-2 rounded-md bg-accent-50 px-3 py-2 text-xs text-accent-700">
                        {{ $engineError }} Inicia el servicio con <code>facial-service\start.bat</code>. Mientras tanto, la asistencia se registra de forma manual.
                    </div>
                @endif
                <div><dt class="text-xs text-slate-500">Vectores biométricos guardados</dt><dd>{{ $templates }}</dd></div>
            </dl>
        </section>

        <section class="card">
            <div class="card-header"><h2 class="card-title">Umbrales de decisión</h2></div>
            <dl class="card-body grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">Coincidencia (FACE_MATCH_THRESHOLD)</dt><dd class="tabular-nums">{{ $config['match_threshold'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">Desconocido bajo (FACE_UNKNOWN_THRESHOLD)</dt><dd class="tabular-nums">{{ $config['unknown_threshold'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">Margen de ambigüedad</dt><dd class="tabular-nums">{{ $config['ambiguity_margin'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">Prueba de presencia</dt><dd>{{ $config['liveness_required'] ? 'Obligatoria (giro de cabeza)' : 'Desactivada' }}</dd></div>
            </dl>
            <p class="border-t border-slate-100 px-5 py-3 text-xs text-amber-700">0.363 es la referencia publicada por OpenCV para SFace. No está validada con la población de North College: calíbrala antes de usar el sistema con estudiantes reales.</p>
        </section>

        <section class="card">
            <div class="card-header"><h2 class="card-title">Protección de datos</h2></div>
            <dl class="card-body grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">Versión de la autorización</dt><dd>{{ $config['consent_version'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">Responsable</dt><dd>{{ $config['controller_name'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">Canal de atención</dt><dd>{{ $config['contact_email'] ?: 'Pendiente de definir' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Conservación tras inactividad</dt><dd>{{ $config['retention_days'] }} días</dd></div>
            </dl>
            <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">La purga se ejecuta con <code>php artisan biometrics:purge</code> (programada a diario). Ver <code>docs/PROTECCION-DATOS.md</code>.</p>
        </section>

        <section class="card">
            <div class="card-header"><h2 class="card-title">Auditoría reciente</h2></div>
            @if ($audit->isEmpty())
                <p class="px-5 py-6 text-center text-sm text-slate-500">Sin operaciones registradas.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach ($audit as $log)
                        <li class="flex items-center justify-between gap-3 px-5 py-2.5">
                            <span class="font-mono text-xs">{{ $log->action }}</span>
                            <span class="text-xs text-slate-500">{{ $log->user?->name ?? 'Sistema' }} · {{ $log->created_at->format('d/m H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
