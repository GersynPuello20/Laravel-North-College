@extends('layouts.app')

@section('title', 'Usuarios y roles')

@section('content')
    <x-page-header title="Usuarios y roles" subtitle="Los cambios de rol y de estado quedan en la auditoría." />

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-4">
            <div class="w-full sm:w-72">
                <label for="q" class="form-label">Buscar</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Nombre o correo" class="form-input">
            </div>
            <div class="w-full sm:w-48">
                <label for="role" class="form-label">Rol</label>
                <select id="role" name="role" class="form-input">
                    <option value="">Todos</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->slug }}" @selected(request('role') === $role->slug)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-secondary"><x-icon name="funnel" class="size-4" /> Filtrar</button>
        </form>

        @if ($users->isEmpty())
            <x-empty-state icon="users" title="No hay usuarios con estos filtros" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Usuario</th><th>Rol y estado</th></tr></thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex flex-wrap items-center gap-2">
                                        @csrf @method('PUT')
                                        <label class="sr-only" for="role-{{ $user->id }}">Rol de {{ $user->name }}</label>
                                        <select id="role-{{ $user->id }}" name="role_id" class="form-input w-48 py-1.5" @disabled($user->is(auth()->user()))>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="sr-only" for="status-{{ $user->id }}">Estado de {{ $user->name }}</label>
                                        <select id="status-{{ $user->id }}" name="status" class="form-input w-36 py-1.5" @disabled($user->is(auth()->user()))>
                                            @foreach (\App\Models\User::STATUS_LABELS as $value => $label)
                                                <option value="{{ $value }}" @selected($user->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @if ($user->is(auth()->user()))
                                            <span class="text-xs text-slate-500">Tu propia cuenta</span>
                                        @else
                                            <button class="btn-secondary btn-sm">Guardar</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        @endif
    </div>
@endsection
