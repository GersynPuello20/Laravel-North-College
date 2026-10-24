@extends('layouts.app')

@section('title', 'Editar asignatura')

@section('content')
    <x-page-header :title="'Editar · '.$subject->name" :back="route('admin.subjects.index')" />

    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="card max-w-2xl">
        @csrf @method('PUT')
        @include('subjects.partials.form')
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('admin.subjects.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection
