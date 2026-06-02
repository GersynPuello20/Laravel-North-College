<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Horario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Horario de Clases</h3>
                
                <div class="grid grid-cols-7 gap-2 mb-4">
                    @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day)
                        <div class="text-center font-semibold text-gray-700 p-2 bg-gray-100 rounded">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>

                <div class="text-center py-12 text-gray-500">
                    <p>No hay clases programadas aún.</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg mt-6 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Materias Inscritas</h3>
                
                <div class="space-y-3">
                    @if($schedule->isEmpty())
                        <p class="text-gray-500 text-center py-8">No hay materias asignadas aún.</p>
                    @else
                        @foreach ($schedule as $subject)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $subject->name }}</h4>
                                        <p class="text-sm text-gray-600">Código: {{ $subject->code ?? 'N/A' }}</p>
                                    </div>
                                    <span class="text-2xl">📖</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
