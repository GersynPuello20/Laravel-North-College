<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel Administrativo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Tarjeta: Total Usuarios -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium">Total de Usuarios</div>
                    <div class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\User::count() }}</div>
                </div>

                <!-- Tarjeta: Total Estudiantes -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium">Estudiantes</div>
                    <div class="text-3xl font-bold text-blue-600 mt-2">{{ \App\Models\Student::count() }}</div>
                </div>

                <!-- Tarjeta: Total Docentes -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium">Docentes</div>
                    <div class="text-3xl font-bold text-green-600 mt-2">{{ \App\Models\User::where('role_id', \App\Models\Role::where('slug', 'teacher')->first()?->id)->count() }}</div>
                </div>

                <!-- Tarjeta: Total Cursos -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium">Cursos</div>
                    <div class="text-3xl font-bold text-purple-600 mt-2">{{ \App\Models\Course::count() }}</div>
                </div>
            </div>

            <!-- Opciones Rápidas -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Gestión del Sistema</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('users.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="font-semibold text-gray-900">👥 Gestionar Usuarios</div>
                            <p class="text-sm text-gray-600 mt-1">Asignar roles y permisos</p>
                        </a>
                        <a href="{{ route('students.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="font-semibold text-gray-900">📚 Gestionar Estudiantes</div>
                            <p class="text-sm text-gray-600 mt-1">Crear y editar estudiantes</p>
                        </a>
                        <a href="{{ route('attendances.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="font-semibold text-gray-900">📋 Asistencia</div>
                            <p class="text-sm text-gray-600 mt-1">Registrar asistencia</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
