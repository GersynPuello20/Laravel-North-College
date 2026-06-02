<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Calificar Estudiantes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ingreso de Calificaciones</h3>
                
                <form action="{{ route('teacher.grades.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="student_id" class="block text-sm font-medium text-gray-700">Estudiante</label>
                            <select name="student_id" id="student_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Selecciona un estudiante --</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Materia</label>
                            <input type="text" name="subject" id="subject" required placeholder="Nombre de la materia" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="grade" class="block text-sm font-medium text-gray-700">Calificación (0-5)</label>
                            <input type="number" name="grade" id="grade" min="0" max="5" step="0.1" required placeholder="Ej: 4.5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Observaciones (Opcional)</label>
                            <input type="text" name="notes" id="notes" placeholder="Ej: Excelente desempeño" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Guardar Calificación
                        </button>
                    </div>
                </form>
            </div>

            <!-- Calificaciones Registradas -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mt-6 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Calificaciones Registradas</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Estudiante</th>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Materia</th>
                                <th class="px-6 py-3 text-center text-gray-700 font-semibold">Calificación</th>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    No hay calificaciones registradas aún.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
