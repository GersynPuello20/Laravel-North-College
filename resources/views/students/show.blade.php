@extends('layouts.app')

@section('title', $student->name)

@section('content')
    <x-page-header :title="$student->name" :subtitle="$student->email" :back="route('students.index')">
        <x-slot:actions>
            <a href="{{ route('students.edit', $student) }}" class="btn-secondary"><x-icon name="pencil-square" class="size-4" /> Editar perfil</a>
            <a href="{{ route('students.biometrics.show', $student) }}" class="btn-primary"><x-icon name="finger-print" class="size-4" /> Enrolamiento facial</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-stat label="Asistencia" :value="$rate !== null ? $rate.' %' : '—'" icon="chart-bar" />
        @foreach (\App\Models\SessionAttendance::STATUS_LABELS as $status => $label)
            <x-stat :label="$label" :value="$totals[$status] ?? 0" />
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6">
            <section class="card">
                <div class="card-header"><h2 class="card-title">Perfil</h2></div>
                <dl class="card-body grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-slate-500">Código</dt><dd>{{ $profile?->enrollment_number ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Documento</dt><dd>{{ $profile?->document_number ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Nacimiento</dt><dd>{{ $profile?->birth_date?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Cuenta</dt><dd>{{ \App\Models\User::STATUS_LABELS[$student->status] ?? $student->status }}</dd></div>
                    @if ($profile?->isMinor())
                        <div class="col-span-2"><dt class="text-xs text-slate-500">Acudiente</dt>
                            <dd>{{ $profile->guardian_name ?? '—' }}<br><span class="text-xs text-slate-500">{{ collect([$profile->guardian_email, $profile->guardian_phone])->filter()->implode(' · ') }}</span></dd>
                        </div>
                    @endif
                    <div class="col-span-2"><dt class="text-xs text-slate-500">Biometría</dt>
                        <dd>
                            @if ($student->faceTemplate) <x-badge tone="green">Enrolado</x-badge>
                            @elseif ($student->activeBiometricConsent) <x-badge tone="amber">Autorizado, sin enrolar</x-badge>
                            @else <x-badge>Sin autorización · registro manual</x-badge>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="card">
                <div class="card-header"><h2 class="card-title">Matrículas</h2></div>
                @if ($student->enrollments->isEmpty())
                    <x-empty-state icon="clipboard-document-list" title="Sin matrículas" />
                @else
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($student->enrollments as $enrollment)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-slate-900">{{ $enrollment->course?->name }}</span>
                                    <span class="block text-xs text-slate-500">Tutor: {{ $enrollment->course?->professor?->name ?? 'Sin asignar' }}</span>
                                </span>
                                <x-badge :tone="$enrollment->status === 'active' ? 'green' : 'slate'">{{ $enrollment->status === 'active' ? 'Activa' : ucfirst($enrollment->status) }}</x-badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <section class="card xl:col-span-2">
            <div class="card-header">
                <h2 class="card-title">Historial de asistencia</h2>
                <a href="{{ route('reports.attendance', ['student' => $student->email, 'from' => now()->subYear()->toDateString()]) }}" class="text-xs font-medium text-navy-700 hover:underline">Abrir en reportes</a>
            </div>
            @if ($records->isEmpty())
                <x-empty-state icon="clipboard-document-check" title="Sin registros de asistencia" />
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Sesión</th><th>Grupo</th><th>Estado</th><th>Registro</th></tr></thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr>
                                    <td class="whitespace-nowrap tabular-nums">
                                        <a href="{{ route('sessions.show', $record->classSession) }}" class="hover:text-navy-700">{{ $record->classSession->starts_at->format('d/m/Y H:i') }}</a>
                                    </td>
                                    <td>{{ $record->classSession->course?->name }}</td>
                                    <td><x-attendance-badge :status="$record->status" /></td>
                                    <td class="text-xs text-slate-500">{{ $record->methodLabel() }} · {{ $record->recordedBy?->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $records->links() }}
            @endif
        </section>
    </div>
@endsection
