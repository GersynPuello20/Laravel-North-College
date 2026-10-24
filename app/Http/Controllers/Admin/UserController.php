<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('role')
            ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")))
            ->when($request->string('role')->value(), fn ($q, $slug) => $q->whereRole($slug))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('id')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'No puedes cambiar el rol ni el estado de tu propia cuenta.');
        }

        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(array_keys(User::STATUS_LABELS))],
        ]);

        $role = Role::findOrFail($data['role_id']);

        // Solo un superadministrador puede crear o quitar otros superadministradores.
        if (($role->slug === 'superadmin' || $user->isSuperAdmin()) && ! $request->user()->isSuperAdmin()) {
            return back()->with('error', 'Solo un superadministrador puede asignar o quitar ese rol.');
        }

        $before = ['role' => $user->role?->slug, 'status' => $user->status];

        $user->role_id = $role->id;
        $user->status = $data['status'];
        $user->save();

        if ($before !== ['role' => $role->slug, 'status' => $user->status]) {
            AuditLog::record('user.permissions_changed', $user, [
                'from' => $before,
                'to' => ['role' => $role->slug, 'status' => $user->status],
            ]);
        }

        return redirect()->route('admin.users.index', $request->only('q', 'role'))->with('success', "Usuario {$user->name} actualizado.");
    }
}
