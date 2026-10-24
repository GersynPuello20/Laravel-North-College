@extends('layouts.app')

@section('title', 'Grupos')

@section('content')
    <x-page-header title="Grupos" subtitle="Cada grupo tiene un tutor, horarios, estudiantes matriculados y sesiones de clase.">
        <x-slot:actions>
            <a href="{{ route('admin.courses.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Nuevo grupo</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        @if ($courses->isEmpty())
            <x-empty-state icon="user-group" title="Aún no hay grupos">
                Crea el primer grupo y asígnale un tutor.
                <x-slot:actions><a href="{{ route('admin.courses.create') }}" class="btn-primary">Nuevo grupo</a></x-slot:actions>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Grupo</th><th>Tutor</th><th class="text-right">Matriculados</th><th class="text-right">Sesiones</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        @foreach ($courses as $course)
                            <tr>
                                <td>
                                    <p class="font-medium text-slate-900">{{ $course->name }}</p>
                                    @if ($course->description)
                                        <p class="max-w-md truncate text-xs text-slate-500">{{ $course->description }}</p>
                                    @endif
                                </td>
                                <td class="text-slate-600">{{ $course->professor?->name ?? 'Sin asignar' }}</td>
                                <td class="text-right tabular-nums">{{ $course->active_students_count }}</td>
                                <td class="text-right tabular-nums">
                                    <a href="{{ route('sessions.index', ['course_id' => $course->id]) }}" class="hover:text-navy-700">{{ $course->class_sessions_count }}</a>
                                </td>
                                <td><x-badge :tone="$course->status === 'active' ? 'green' : 'slate'">{{ ['active' => 'Activo', 'pending' => 'Pendiente', 'suspended' => 'Suspendido'][$course->status] ?? $course->status }}</x-badge></td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('admin.enrollments.index', ['course_id' => $course->id]) }}" class="btn-ghost btn-sm">Matrículas</a>
                                    <a href="{{ route('admin.courses.edit', $course) }}" class="btn-ghost btn-sm"><x-icon name="pencil-square" class="size-4" /> Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $courses->links() }}
        @endif
    </div>
@endsection
