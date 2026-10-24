@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-blue-950">Notas del docente</h1>
            <p class="text-gray-500 mt-2">Registra y revisa las notas asignadas a tus estudiantes.</p>
        </div>
        <a href="{{ route('teacher.grades.create') }}" class="rounded-xl bg-blue-950 text-white px-6 py-3 text-sm font-semibold hover:bg-red-600 transition">Nueva nota</a>
    </div>

    <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estudiante</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Asignatura</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nota</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($grades as $grade)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $grade->student?->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $grade->subject?->name ?? 'Sin asignatura' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $grade->grade }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $grade->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('teacher.grades.edit', $grade) }}" class="text-blue-950 hover:text-red-600">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">No hay notas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50">{{ $grades->links() }}</div>
    </div>
</div>
@endsection
