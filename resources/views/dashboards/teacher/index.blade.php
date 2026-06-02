<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel del Docente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Docente -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bienvenido, {{ auth()->user()->name }}</h3>
                <p class="text-gray-600">Como docente, puedes gestionar tus cursos, calificar estudiantes y más.</p>
            </div>

            <!-- Opciones Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <!-- Mis Cursos -->
                <a href="{{ route('teacher.courses.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 hover:shadow-md transition">
                    <div class="text-2xl mb-2">📚</div>
                    <h3 class="text-lg font-semibold text-gray-900">Mis Cursos</h3>
                    <p class="text-sm text-gray-600 mt-2">Gestionar mis cursos</p>
                </a>

                <!-- Calificar Estudiantes -->
                <a href="{{ route('teacher.grades') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 hover:shadow-md transition">
                    <div class="text-2xl mb-2">📝</div>
                    <h3 class="text-lg font-semibold text-gray-900">Calificar Estudiantes</h3>
                    <p class="text-sm text-gray-600 mt-2">Ingresar calificaciones</p>
                </a>

                <!-- Mi Horario -->
                <a href="{{ route('teacher.schedule') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 hover:shadow-md transition">
                    <div class="text-2xl mb-2">📅</div>
                    <h3 class="text-lg font-semibold text-gray-900">Mi Horario</h3>
                    <p class="text-sm text-gray-600 mt-2">Ver horario de mis clases</p>
                </a>
            </div>

            <!-- Cursos Rápidos -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Mis Cursos Activos</h3>
                    <a href="{{ route('teacher.courses.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        ➕ Crear Curso
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Código</th>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Nombre del Curso</th>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Estudiantes</th>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr class="hover:bg-gray-50">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    Aún no tienes cursos. <a href="{{ route('teacher.courses.create') }}" class="text-blue-600 hover:underline">Crea uno aquí</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
