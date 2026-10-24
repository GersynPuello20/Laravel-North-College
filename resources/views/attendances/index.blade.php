@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-blue-950">Asistencia</h1>
                <p class="text-gray-500 mt-2">Historial de registros de entrada y salida para estudiantes.</p>
            </div>
            <a href="{{ route('admin.attendances.create') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-950 px-5 py-3 text-white transition hover:bg-blue-700">
                Registrar asistencia
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">#</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estudiante</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Tipo</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Registro</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nota</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($attendances as $attendance)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $attendance->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ optional($attendance->user)->name ?? 'Usuario eliminado' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 capitalize">{{ $attendance->attendance_type }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ optional($attendance->recorded_at)->format('d/m/Y H:i') ?? 'Sin registro' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $attendance->note ?? '-' }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <a href="{{ route('admin.attendances.edit', $attendance) }}" class="text-blue-600 hover:text-blue-800">Editar</a>
                                    <form action="{{ route('admin.attendances.destroy', $attendance) }}" method="POST" id="delete-attendance-form-{{ $attendance->id }}" class="inline-block ml-4">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" data-form-id="delete-attendance-form-{{ $attendance->id }}" data-attendance-name="{{ optional($attendance->user)->name ?? 'registro de asistencia' }}" class="delete-attendance-button text-red-600 hover:text-red-800">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">No hay registros de asistencia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-slate-50">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>

    <div id="delete-attendance-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 px-4 py-6">
        <div class="w-full max-w-xl rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 overflow-hidden transform -translate-y-6">
            <div class="bg-red-600 px-6 py-5 text-white">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-700/90 text-2xl">!</div>
                    <div>
                        <h2 class="text-xl font-semibold">Eliminar asistencia</h2>
                        <p class="text-sm text-red-100">Esta acción es irreversible.</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-6">
                <p class="text-gray-700">¿Estás seguro de que quieres eliminar el registro de <span id="delete-attendance-name" class="font-semibold text-slate-900"></span>?</p>
                <p class="mt-2 text-sm text-gray-500">El registro se eliminará permanentemente del sistema.</p>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" id="cancel-delete-attendance" class="inline-flex justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancelar</button>
                <button type="button" id="confirm-delete-attendance" class="inline-flex justify-center rounded-2xl bg-red-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-red-700">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('delete-attendance-modal');
            const attendanceName = document.getElementById('delete-attendance-name');
            const confirmButton = document.getElementById('confirm-delete-attendance');
            const cancelButton = document.getElementById('cancel-delete-attendance');
            let activeForm = null;

            document.querySelectorAll('.delete-attendance-button').forEach(function (button) {
                button.addEventListener('click', function () {
                    activeForm = document.getElementById(button.dataset.formId);
                    attendanceName.textContent = button.dataset.attendanceName;
                    modal.classList.remove('hidden');
                });
            });

            confirmButton.addEventListener('click', function () {
                if (activeForm) {
                    activeForm.submit();
                }
            });

            cancelButton.addEventListener('click', function () {
                modal.classList.add('hidden');
                activeForm = null;
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                    activeForm = null;
                }
            });
        });
    </script>
@endsection
