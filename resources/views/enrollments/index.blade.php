@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-blue-950">Matrículas</h1>
            <p class="text-gray-500 mt-2">Todas las matrículas registradas en el sistema.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estudiante</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Curso</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($enrollments as $enrollment)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $enrollment->student?->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $enrollment->course?->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $enrollment->enrollment_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($enrollment->status) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <form action="{{ route('admin.enrollments.destroy', $enrollment) }}" method="POST" class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">No hay matrículas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50">{{ $enrollments->links() }}</div>
    </div>
</div>
@endsection
