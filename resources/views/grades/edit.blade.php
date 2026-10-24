@extends('layouts.app')

@section('title', 'Editar nota')

@section('content')
    <x-page-header title="Editar nota" :back="route('teacher.grades.index')" />

    <form method="POST" action="{{ route('teacher.grades.update', $grade) }}" class="card max-w-2xl">
        @csrf @method('PUT')
        @include('grades.partials.form')
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('teacher.grades.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection
