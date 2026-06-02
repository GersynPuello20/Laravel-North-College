<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Curso') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <form action="{{ route('teacher.courses.update', $course) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Curso *</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $course->name) }}" placeholder="Ej: Matemáticas Avanzadas" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="p-4 bg-gray-100 rounded-md">
                        <label for="code" class="block text-sm font-medium text-gray-700">Código del Curso</label>
                        <input type="text" name="code" id="code" disabled value="{{ $course->code }}" class="mt-1 block w-full bg-gray-200 border-gray-300 rounded-md text-gray-600">
                        <p class="text-xs text-gray-500 mt-1">El código no puede ser modificado</p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
                        <textarea name="description" id="description" rows="4" placeholder="Describe el contenido y objetivos del curso..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $course->description) }}</textarea>
                        @error('description')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700">Capacidad Máxima (Opcional)</label>
                        <input type="number" name="capacity" id="capacity" min="1" value="{{ old('capacity', $course->capacity) }}" placeholder="Ej: 30" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('capacity')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <a href="{{ route('teacher.courses.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Actualizar Curso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
