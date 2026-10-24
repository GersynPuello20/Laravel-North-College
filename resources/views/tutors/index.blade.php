@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-blue-950">Docentes</h1>
                <p class="text-gray-500 mt-2">Listado de usuarios con rol de docente. La gestión de roles se realiza desde Usuarios.</p>
            </div>
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
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nombre</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Email</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($tutors as $tutor)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $tutor->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $tutor->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $tutor->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @if($tutor->status === 'active')
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Activo</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">No hay docentes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-slate-50">
                {{ $tutors->links() }}
            </div>
        </div>
    </div>
@endsection
