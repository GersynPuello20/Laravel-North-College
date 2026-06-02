<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel del Estudiante') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Estudiante -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Perfil</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-sm text-gray-600">Nombre</div>
                        <div class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Correo</div>
                        <div class="text-lg font-semibold text-gray-900">{{ auth()->user()->email }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Estado</div>
                        <div class="text-lg font-semibold">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ ucfirst(auth()->user()->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opciones Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Mi Horario -->
                <a href="{{ route('student.schedule') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 hover:shadow-md transition">
                    <div class="text-2xl mb-2">📅</div>
                    <h3 class="text-lg font-semibold text-gray-900">Mi Horario</h3>
                    <p class="text-sm text-gray-600 mt-2">Ver el horario de mis materias</p>
                </a>

                <!-- Mis Calificaciones -->
                <a href="{{ route('student.grades') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 hover:shadow-md transition">
                    <div class="text-2xl mb-2">📊</div>
                    <h3 class="text-lg font-semibold text-gray-900">Mis Calificaciones</h3>
                    <p class="text-sm text-gray-600 mt-2">Consultar mis calificaciones</p>
                </a>
            </div>

            <!-- Resumen de Materias -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mt-6 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Mis Materias</h3>
                <div class="text-center py-8 text-gray-500">
                    <p>No hay materias asignadas aún.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
