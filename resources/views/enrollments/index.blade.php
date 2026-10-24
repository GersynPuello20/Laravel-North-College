@extends('layouts.app')

@section('title', 'Matrículas')

@section('content')
    <x-page-header title="Matrículas" subtitle="Solo los estudiantes con matrícula activa cuentan para la asistencia del grupo." />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card h-fit">
            <div class="card-header"><h2 class="card-title">Matricular estudiantes</h2></div>
            <form method="GET" class="border-b border-slate-100 px-5 pt-5 pb-4">
                <label for="filter-course" class="form-label">Grupo</label>
                <select id="filter-course" name="course_id" class="form-input" onchange="this.form.submit()">
                    <option value="">Selecciona un grupo</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected($courseId == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
                <noscript><button class="btn-secondary btn-sm mt-2">Elegir</button></noscript>
            </form>

            @if ($courseId)
                <form method="POST" action="{{ route('admin.enrollments.store') }}" class="card-body space-y-3" x-data="{ q: '' }">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $courseId }}">
                    @if ($students->isEmpty())
                        <p class="text-sm text-slate-500">Todos los estudiantes activos ya están matriculados en este grupo.</p>
                    @else
                        <input type="search" x-model="q" placeholder="Filtrar por nombre" class="form-input" aria-label="Filtrar estudiantes">
                        <div class="max-h-72 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-2">
                            @foreach ($students as $student)
                                <label class="flex items-center gap-3 rounded px-2 py-1.5 text-sm hover:bg-slate-50"
                                       x-show="@js(mb_strtolower($student->name.' '.$student->email)).includes(q.toLowerCase())">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-checkbox">
                                    <span class="min-w-0">
                                        <span class="block truncate">{{ $student->name }}</span>
                                        <span class="block truncate text-xs text-slate-500">{{ $student->email }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <button class="btn-primary w-full">Matricular seleccionados</button>
                    @endif
                </form>
            @else
                <p class="card-body text-sm text-slate-500">Elige un grupo para ver los estudiantes que puedes matricular.</p>
            @endif
        </section>

        <section class="card xl:col-span-2">
            <div class="card-header">
                <h2 class="card-title">{{ $courseId ? 'Matriculados en '.$courses->firstWhere('id', $courseId)?->name : 'Todas las matrículas' }}</h2>
                @if ($courseId)
                    <a href="{{ route('admin.enrollments.index') }}" class="text-xs font-medium text-navy-700 hover:underline">Ver todas</a>
                @endif
            </div>
            @if ($enrollments->isEmpty())
                <x-empty-state icon="clipboard-document-list" title="No hay matrículas" />
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Estudiante</th><th>Grupo</th><th>Fecha</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                        <tbody>
                            @foreach ($enrollments as $enrollment)
                                <tr>
                                    <td>
                                        <p class="font-medium text-slate-900">{{ $enrollment->student?->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $enrollment->student?->email }}</p>
                                    </td>
                                    <td>{{ $enrollment->course?->name }}</td>
                                    <td class="tabular-nums text-slate-500">{{ $enrollment->enrollment_date?->format('d/m/Y') }}</td>
                                    <td><x-badge :tone="$enrollment->status === 'active' ? 'green' : 'slate'">{{ $enrollment->status === 'active' ? 'Activa' : ucfirst($enrollment->status) }}</x-badge></td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('admin.enrollments.destroy', $enrollment) }}" onsubmit="return confirm('¿Retirar esta matrícula? Los registros de asistencia se conservan.')">
                                            @csrf @method('DELETE')
                                            <button class="btn-ghost btn-sm" title="Retirar matrícula"><x-icon name="trash" class="size-4" /></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $enrollments->links() }}
            @endif
        </section>
    </div>
@endsection
