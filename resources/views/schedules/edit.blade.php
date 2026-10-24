@extends('layouts.app')

@section('title', 'Editar horario')

@section('content')
    <x-page-header title="Editar horario" :back="route('admin.schedules.index')" />

    <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="card max-w-2xl">
        @csrf @method('PUT')
        @include('schedules.partials.form')
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('admin.schedules.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection
