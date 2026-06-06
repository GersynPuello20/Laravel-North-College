@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-blue-950">Usuarios registrados</h1>
                <p class="text-gray-500 mt-2">Listado de usuarios del sistema y asignación de roles.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 text-blue-700">
                {{ session('info') }}
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
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Rol actual</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $user->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $user->role?->name ?? 'Sin asignar' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @if($user->status === 'active')
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Activo</span>
                                    @elseif($user->status === 'suspended')
                                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Suspendido</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="inline-flex items-center gap-3">
                                        @csrf
                                        @method('PUT')
                                        <select name="role_id" class="rounded-xl border border-gray-200 bg-slate-50 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <select name="status" class="rounded-xl border border-gray-200 bg-slate-50 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                                            <option value="active" @selected($user->status === 'active')>Activo</option>
                                            <option value="pending" @selected($user->status === 'pending')>Pendiente</option>
                                            <option value="suspended" @selected($user->status === 'suspended')>Suspendido</option>
                                        </select>
                                        <button type="submit" class="rounded-xl bg-blue-950 px-4 py-2 text-white transition hover:bg-blue-700">Guardar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">No hay usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-slate-50">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
