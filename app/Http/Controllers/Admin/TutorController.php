<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class TutorController extends Controller
{
    public function index()
    {
        $tutors = User::with('role')
            ->whereHas('role', fn ($query) => $query->where('slug', 'teacher'))
            ->latest()
            ->paginate(15);

        return view('tutors.index', compact('tutors'));
    }
}
