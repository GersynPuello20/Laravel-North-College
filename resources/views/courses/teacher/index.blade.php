<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Cursos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Cursos Disponibles</h3>
                    <a href="{{ route('teacher.courses.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        ➕ Crear Nuevo Curso
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Código</th>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Nombre del Curso</th>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Descripción</th>
                                <th class="px-6 py-3 text-center text-gray-700 font-semibold">Capacidad</th>
                                <th class="px-6 py-3 text-center text-gray-700 font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($courses as $course)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-gray-900 font-medium">{{ $course->code }}</td>
                                    <td class="px-6 py-4 text-gray-900">{{ $course->name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $course->description ?? '-' }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600">{{ $course->capacity ?? '-' }}</td>
                                    <td class="px-6 py-4 text-center space-x-2">
                                        <a href="{{ route('teacher.courses.edit', $course) }}" class="text-blue-600 hover:text-blue-900 font-semibold">Editar</a>
                                        <form action="{{ route('teacher.courses.destroy', $course) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No has creado cursos aún. <a href="{{ route('teacher.courses.create') }}" class="text-blue-600 hover:underline">Crea uno aquí</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
