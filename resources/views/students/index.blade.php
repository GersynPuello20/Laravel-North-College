@extends('layouts.app')

@section('title', 'Estudiantes')

@section('content')
    <x-page-header title="Estudiantes" subtitle="Usuarios con rol estudiante. Las cuentas se crean por registro y el rol se asigna en Usuarios y roles." />

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-4">
            <div class="w-full sm:w-64">
                <label for="q" class="form-label">Buscar</label>
                <div class="relative">
                    <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre, correo, código o documento" class="form-input pl-9">
                </div>
            </div>
            <div class="w-full sm:w-48">
                <label for="course_id" class="form-label">Grupo</label>
                <select id="course_id" name="course_id" class="form-input">
                    <option value="">Todos</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected(($filters['course_id'] ?? null) == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-48">
                <label for="biometric" class="form-label">Biometría</label>
                <select id="biometric" name="biometric" class="form-input">
                    <option value="">Todos</option>
                    <option value="enrolled" @selected(($filters['biometric'] ?? null) === 'enrolled')>Enrolados</option>
                    <option value="consent" @selected(($filters['biometric'] ?? null) === 'consent')>Autorizados sin enrolar</option>
                    <option value="none" @selected(($filters['biometric'] ?? null) === 'none')>Sin autorización</option>
                </select>
            </div>
            <div class="w-full sm:w-36">
                <label for="status" class="form-label">Cuenta</label>
                <select id="status" name="status" class="form-input">
                    <option value="">Todas</option>
                    @foreach (\App\Models\User::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button class="btn-secondary"><x-icon name="funnel" class="size-4" /> Filtrar</button>
                @if (array_filter($filters))
                    <a href="{{ route('students.index') }}" class="btn-ghost">Limpiar</a>
                @endif
            </div>
        </form>

        @if ($students->isEmpty())
            <x-empty-state icon="academic-cap" title="No hay estudiantes con estos filtros" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Código</th>
                            <th class="text-right">Matrículas</th>
                            <th>Biometría</th>
                            <th>Cuenta</th>
                            <th><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>
                                    <a href="{{ route('students.show', $student) }}" class="font-medium text-slate-900 hover:text-navy-700">{{ $student->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $student->email }}</p>
                                </td>
                                <td class="tabular-nums text-slate-500">{{ $student->student?->enrollment_number ?? '—' }}</td>
                                <td class="text-right tabular-nums">{{ $student->active_enrollments_count }}</td>
                                <td>
                                    @if ($student->faceTemplate)
                                        <x-badge tone="green">Enrolado</x-badge>
                                    @elseif ($student->activeBiometricConsent)
                                        <x-badge tone="amber">Autorizado</x-badge>
                                    @else
                                        <x-badge>Manual</x-badge>
                                    @endif
                                </td>
                                <td>
                                    <x-badge :tone="$student->isActive() ? 'green' : 'slate'">{{ \App\Models\User::STATUS_LABELS[$student->status] ?? $student->status }}</x-badge>
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('students.biometrics.show', $student) }}" class="btn-ghost btn-sm" title="Enrolamiento facial"><x-icon name="finger-print" class="size-4" /></a>
                                    <a href="{{ route('students.show', $student) }}" class="btn-ghost btn-sm">Ver <x-icon name="chevron-right" class="size-4" /></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $students->links() }}
        @endif
    </div>
@endsection
