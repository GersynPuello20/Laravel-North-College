<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Calificaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Historial de Calificaciones</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Materia</th>
                                <th class="px-6 py-3 text-left text-gray-700 font-semibold">Docente</th>
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

                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        💡 <strong>Tip:</strong> Las calificaciones serán actualizadas por tus docentes durante el semestre.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
