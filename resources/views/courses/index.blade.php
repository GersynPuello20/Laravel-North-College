@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-blue-950">Cursos</h1>
            <p class="text-gray-500 mt-2">Administra los cursos disponibles en la academia.</p>
        </div>

        <a href="{{ route('admin.courses.create') }}" class="rounded-xl bg-blue-950 text-white px-6 py-3 text-sm font-semibold hover:bg-red-600 transition">Nuevo curso</a>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nombre</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Profesor</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($courses as $course)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $course->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $course->professor?->name ?? 'Sin asignar' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">{{ ucfirst($course->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('admin.courses.edit', $course) }}" class="text-blue-950 hover:text-red-600">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">No hay cursos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
