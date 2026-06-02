<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('dashboards.teacher.index', [
            'user' => $user,
        ]);
    }
}
