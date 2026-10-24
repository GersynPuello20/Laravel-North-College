@extends('layouts.app')

@section('title', 'Nueva asignatura')

@section('content')
    <x-page-header title="Nueva asignatura" :back="route('admin.subjects.index')" />

    <form method="POST" action="{{ route('admin.subjects.store') }}" class="card max-w-2xl">
        @csrf
        @include('subjects.partials.form', ['subject' => null])
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('admin.subjects.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Crear asignatura</button>
        </div>
    </form>
@endsection
