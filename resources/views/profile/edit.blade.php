@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    <x-page-header title="Mi perfil" subtitle="Tus datos de acceso a North College." />

    <div class="max-w-3xl space-y-6">
        <section class="card"><div class="card-body max-w-xl">@include('profile.partials.update-profile-information-form')</div></section>
        <section class="card"><div class="card-body max-w-xl">@include('profile.partials.update-password-form')</div></section>
        <section class="card"><div class="card-body max-w-xl">@include('profile.partials.delete-user-form')</div></section>
    </div>
@endsection
