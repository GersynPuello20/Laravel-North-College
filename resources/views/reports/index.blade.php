@extends('layouts.app')

@section('title', 'Reportes de asistencia')

@section('content')
    @php $exportQuery = array_filter(array_merge($filters, ['from' => $from->toDateString(), 'to' => $to->toDateString()])); @endphp

    <x-page-header title="Reportes de asistencia" :subtitle="'Del '.$from->format('d/m/Y').' al '.$to->format('d/m/Y').(auth()->user()->isTeacher() ? ' · solo tus grupos' : '')">
        <x-slot:actions>
            <div x-data="{ open: false }" class="relative">
                <button type="button" class="btn-secondary" @click="open = !open" :aria-expanded="open">
                    <x-icon name="arrow-down-tray" class="size-4" /> Exportar
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-10 mt-2 w-44 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-lg">
                    <a href="{{ route('reports.attendance.export', array_merge($exportQuery, ['format' => 'xlsx'])) }}" class="block px-4 py-2 hover:bg-slate-50">Excel (.xlsx)</a>
                    <a href="{{ route('reports.attendance.export', array_merge($exportQuery, ['format' => 'csv'])) }}" class="block px-4 py-2 hover:bg-slate-50">CSV</a>
                    <a href="{{ route('reports.attendance.export', array_merge($exportQuery, ['format' => 'pdf'])) }}" class="block px-4 py-2 hover:bg-slate-50">PDF</a>
                </div>
            </div>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="from" class="form-label">Desde</label>
            <input id="from" type="date" name="from" value="{{ $from->toDateString() }}" class="form-input">
        </div>
        <div>
            <label for="to" class="form-label">Hasta</label>
            <input id="to" type="date" name="to" value="{{ $to->toDateString() }}" class="form-input">
        </div>
        <div>
            <label for="course_id" class="form-label">Grupo</label>
            <select id="course_id" name="course_id" class="form-input">
                <option value="">Todos</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected(($filters['course_id'] ?? null) == $course->id)>{{ $course->name }}</option>
                @endforeach
            </select>
        </div>
        @if ($teachers->isNotEmpty())
            <div>
                <label for="teacher_id" class="form-label">Tutor</label>
                <select id="teacher_id" name="teacher_id" class="form-input">
                    <option value="">Todos</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(($filters['teacher_id'] ?? null) == $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label for="student" class="form-label">Estudiante</label>
            <input id="student" name="student" value="{{ $filters['student'] ?? '' }}" placeholder="Nombre o correo" class="form-input">
        </div>
        <div>
            <label for="status" class="form-label">Estado</label>
            <select id="status" name="status" class="form-input">
                <option value="">Todos</option>
                @foreach (\App\Models\SessionAttendance::STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="method" class="form-label">Método</label>
            <select id="method" name="method" class="form-input">
                <option value="">Todos</option>
                @foreach (\App\Models\SessionAttendance::METHOD_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['method'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col justify-end gap-2 text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" name="corrected" value="1" class="form-checkbox" @checked(! empty($filters['corrected']))> Solo corregidos</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="needs_review" value="1" class="form-checkbox" @checked(! empty($filters['needs_review']))> Pendientes de revisión</label>
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
            <button class="btn-primary"><x-icon name="funnel" class="size-4" /> Aplicar filtros</button>
            <a href="{{ route('reports.attendance') }}" class="btn-ghost">Últimos 30 días</a>
        </div>
    </form>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-stat label="Asistencia" :value="$rate !== null ? $rate.' %' : '—'" icon="chart-bar" :hint="$totals->sum().' registros'" />
        <x-stat label="Presente" :value="$totals['present']" tone="green" />
        <x-stat label="Tarde" :value="$totals['late']" tone="amber" />
        <x-stat label="Ausente" :value="$totals['absent']" tone="red" />
        <x-stat label="Justificada" :value="$totals['excused']" tone="slate" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-4">
        <section class="card xl:col-span-3">
            <div class="card-header"><h2 class="card-title">Registros</h2></div>
            @if ($records->isEmpty())
                <x-empty-state icon="chart-bar" title="No hay registros con estos filtros">
                    Los reportes muestran solo asistencias registradas en sesiones del periodo.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr><th>Sesión</th><th>Estudiante</th><th>Estado</th><th>Registro</th><th>Correcciones</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                @php $session = $record->classSession; $last = $record->corrections->first(); @endphp
                                <tr>
                                    <td class="whitespace-nowrap">
                                        <a href="{{ route('sessions.show', $session) }}" class="font-medium text-slate-900 hover:text-navy-700">{{ $session->course?->name }}</a>
                                        <p class="text-xs tabular-nums text-slate-500">{{ $session->starts_at->format('d/m/Y H:i') }} · {{ $session->course?->professor?->name ?? 'Sin tutor' }}</p>
                                    </td>
                                    <td>{{ $record->student?->name }}</td>
                                    <td><x-attendance-badge :status="$record->status" /></td>
                                    <td class="text-xs text-slate-500">
                                        {{ $record->methodLabel() }} · {{ $record->recorded_at->format('H:i') }}<br>{{ $record->recordedBy?->name }}
                                    </td>
                                    <td class="text-xs text-slate-500">
                                        @if ($last)
                                            {{ $record->corrections->count() }} · {{ $last->changedBy?->name }}: {{ \Illuminate\Support\Str::limit($last->reason, 60) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $records->links() }}
            @endif
        </section>

        <section class="card h-fit">
            <div class="card-header"><h2 class="card-title">Reconocimiento sin registro</h2></div>
            @if ($failures->isEmpty())
                <p class="px-5 py-6 text-center text-sm text-slate-500">Sin intentos fallidos en el periodo.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach ($failures as $result => $total)
                        <li class="flex items-center justify-between px-5 py-2.5">
                            <span>{{ $failureLabels[$result] ?? $result }}</span>
                            <span class="font-semibold tabular-nums">{{ $total }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">Cada intento sin registro debe resolverse con registro manual.</p>
            @endif
        </section>
    </div>
@endsection
