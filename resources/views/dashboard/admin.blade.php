@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-blue-950">Dashboard Super Administrador</h1>
    <p class="text-gray-500 mt-2">Monitoreo del sistema académico y gestión de usuarios, cursos y calificaciones.</p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-blue-900">
        <p class="text-gray-500">Estudiantes registrados</p>
        <h2 class="text-4xl font-bold mt-3">{{ $stats['students'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-red-600">
        <p class="text-gray-500">Profesores activos</p>
        <h2 class="text-4xl font-bold mt-3">{{ $stats['teachers'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-green-500">
        <p class="text-gray-500">Cursos disponibles</p>
        <h2 class="text-4xl font-bold mt-3">{{ $stats['courses'] }}</h2>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-yellow-500">
        <p class="text-gray-500">Matrículas totales</p>
        <h2 class="text-4xl font-bold mt-3">{{ $stats['enrollments'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-slate-700">
        <p class="text-gray-500">Asignaturas registradas</p>
        <h2 class="text-4xl font-bold mt-3">{{ $stats['subjects'] }}</h2>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border-l-8 border-indigo-500">
        <p class="text-gray-500">Notas registradas</p>
        <h2 class="text-4xl font-bold mt-3">{{ $stats['grades'] }}</h2>
    </div>
</div>
@endsection
