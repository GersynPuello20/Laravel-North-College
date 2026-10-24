@extends('layouts.app')

@section('title', 'Mis grupos')

@section('content')
    <x-page-header title="Mis grupos" subtitle="Grupos en los que eres tutor." />

    <div class="card">
        @if ($courses->isEmpty())
            <x-empty-state icon="user-group" title="No tienes grupos asignados">
                Un administrador debe asignarte como tutor de un grupo.
            </x-empty-state>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($courses as $course)
                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">{{ $course->name }}</p>
                            <p class="text-xs text-slate-500">{{ $course->students_count }} matriculados{{ $course->description ? ' · '.$course->description : '' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('sessions.index', ['course_id' => $course->id]) }}" class="btn-secondary btn-sm"><x-icon name="calendar-days" class="size-4" /> Sesiones</a>
                            <a href="{{ route('sessions.create', ['course_id' => $course->id]) }}" class="btn-primary btn-sm"><x-icon name="plus" class="size-4" /> Nueva sesión</a>
                        </div>
                    </li>
                @endforeach
            </ul>
            {{ $courses->links() }}
        @endif
    </div>
@endsection
