@extends('layouts.app')

@section('title', 'Editar grupo')

@section('content')
    <x-page-header :title="'Editar · '.$course->name" :back="route('admin.courses.index')" />

    <form method="POST" action="{{ route('admin.courses.update', $course) }}" class="card max-w-2xl">
        @csrf @method('PUT')
        @include('courses.partials.form')
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('admin.courses.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Guardar cambios</button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="mt-6 max-w-2xl"
          onsubmit="return confirm('Se eliminarán el grupo, sus horarios, matrículas, sesiones y registros de asistencia. ¿Continuar?')">
        @csrf @method('DELETE')
        <div class="card flex flex-wrap items-center justify-between gap-3 border-accent-200 px-5 py-4">
            <p class="text-sm text-slate-600">Eliminar el grupo borra también su historial de asistencia.</p>
            <button class="btn-danger"><x-icon name="trash" class="size-4" /> Eliminar grupo</button>
        </div>
    </form>
@endsection
