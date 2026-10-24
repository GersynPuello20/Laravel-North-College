@extends('layouts.app')

@section('title', 'Nueva nota')

@section('content')
    <x-page-header title="Nueva nota" :back="route('teacher.grades.index')" />

    <form method="POST" action="{{ route('teacher.grades.store') }}" class="card max-w-2xl">
        @csrf
        @include('grades.partials.form', ['grade' => null])
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('teacher.grades.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Registrar nota</button>
        </div>
    </form>
@endsection
