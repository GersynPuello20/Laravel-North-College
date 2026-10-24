@extends('layouts.app')

@section('title', 'Editar estudiante')

@section('content')
    <x-page-header :title="'Perfil de '.$student->name" subtitle="Nombre y correo se cambian desde la cuenta del usuario." :back="route('students.show', $student)" />

    <form method="POST" action="{{ route('students.update', $student) }}" class="card max-w-3xl">
        @csrf @method('PUT')
        <div class="card-body grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-field label="Código estudiantil" name="enrollment_number">
                <input id="enrollment_number" name="enrollment_number" value="{{ old('enrollment_number', $profile->enrollment_number) }}" maxlength="30" class="form-input">
            </x-field>
            <x-field label="Documento de identidad" name="document_number">
                <input id="document_number" name="document_number" value="{{ old('document_number', $profile->document_number) }}" maxlength="30" class="form-input">
            </x-field>
            <x-field label="Fecha de nacimiento" name="birth_date" hint="Necesaria para saber si la autorización biométrica la da el estudiante o su representante legal.">
                <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', $profile->birth_date?->toDateString()) }}" max="{{ today()->subDay()->toDateString() }}" class="form-input">
            </x-field>

            <div class="sm:col-span-2 border-t border-slate-100 pt-5">
                <h2 class="text-sm font-semibold text-slate-900">Representante legal / acudiente</h2>
                <p class="text-xs text-slate-500">Obligatorio en la práctica para estudiantes menores de edad.</p>
            </div>
            <x-field label="Nombre" name="guardian_name">
                <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name', $profile->guardian_name) }}" maxlength="255" class="form-input">
            </x-field>
            <x-field label="Documento" name="guardian_document">
                <input id="guardian_document" name="guardian_document" value="{{ old('guardian_document', $profile->guardian_document) }}" maxlength="30" class="form-input">
            </x-field>
            <x-field label="Correo" name="guardian_email">
                <input id="guardian_email" type="email" name="guardian_email" value="{{ old('guardian_email', $profile->guardian_email) }}" maxlength="255" class="form-input">
            </x-field>
            <x-field label="Teléfono" name="guardian_phone">
                <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $profile->guardian_phone) }}" maxlength="30" class="form-input">
            </x-field>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('students.show', $student) }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary">Guardar</button>
        </div>
    </form>
@endsection
