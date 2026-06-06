@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-blue-950">Asignaturas</h1>
            <p class="text-gray-500 mt-2">Gestiona las asignaturas asociadas a los cursos.</p>
        </div>

        <a href="{{ route('admin.subjects.create') }}" class="rounded-xl bg-blue-950 text-white px-6 py-3 text-sm font-semibold hover:bg-red-600 transition">Nueva asignatura</a>
    </div>

    <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nombre</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Curso</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($subjects as $subject)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $subject->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $subject->course?->name ?? 'Sin curso' }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-blue-950 hover:text-red-600">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500">No hay asignaturas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50">
            {{ $subjects->links() }}
        </div>
    </div>
</div>
@endsection
