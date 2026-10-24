@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-blue-950">Editar estudiante</h1>
            <p class="text-gray-500 mt-2">Actualiza los datos del estudiante.</p>
        </div>

        <div class="rounded-3xl bg-white p-8 shadow-lg">
            <form action="{{ route('students.update', $student) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @include('students._form')

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-xl bg-blue-950 px-6 py-3 text-white transition hover:bg-blue-700">Actualizar estudiante</button>
                    <a href="{{ route('students.index') }}" class="text-gray-600 hover:text-gray-900">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
