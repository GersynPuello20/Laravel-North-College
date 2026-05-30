<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest()->paginate(15);
        $roles = Role::orderBy('id')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($data['role_id']);
        $user->role_id = $role->id;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Rol actualizado correctamente.');
    }
}
