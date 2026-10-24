@extends('layouts.app')

@section('title', 'Nuevo horario')

@section('content')
    <x-page-header title="Nuevo horario" :back="route('admin.schedules.index')" />

    <form method="POST" action="{{ route('admin.schedules.store') }}" class="card max-w-2xl">
        @csrf
        @include('schedules.partials.form', ['schedule' => null])
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('admin.schedules.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Crear horario</button>
        </div>
    </form>
@endsection
