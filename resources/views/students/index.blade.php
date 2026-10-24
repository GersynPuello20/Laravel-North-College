@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-blue-950">Estudiantes</h1>
                <p class="text-gray-500 mt-2">Listado de usuarios con rol de estudiante. La asignación y edición de roles se gestiona desde Usuarios.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 text-blue-700">
                {{ session('info') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">#</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nombre</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Email</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Rol</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($students as $student)
                            <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $student->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $student->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $student->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $student->role?->name ?? 'Estudiante' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @if($student->status === 'active')
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Activo</span>
                                @else
                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">No hay estudiantes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-slate-50">
                {{ $students->links() }}
            </div>
        </div>
    </div>

@endsection
