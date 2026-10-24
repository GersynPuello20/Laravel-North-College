@extends('layouts.app')

@section('title', 'Nuevo grupo')

@section('content')
    <x-page-header title="Nuevo grupo" :back="route('admin.courses.index')" />

    <form method="POST" action="{{ route('admin.courses.store') }}" class="card max-w-2xl">
        @csrf
        @include('courses.partials.form', ['course' => null])
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('admin.courses.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Crear grupo</button>
        </div>
    </form>
@endsection
