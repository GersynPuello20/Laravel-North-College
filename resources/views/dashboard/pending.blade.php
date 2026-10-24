@extends('layouts.app')

@section('title', 'Cuenta pendiente')

@section('content')
    <div class="card mx-auto max-w-xl">
        <x-empty-state icon="lock-closed" title="Tu cuenta aún no tiene un rol asignado">
            Un administrador debe asignarte un rol (estudiante, docente o coordinador) para que puedas usar el sistema.
        </x-empty-state>
    </div>
@endsection
