<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = $user->student;

        return view('dashboards.student.index', [
            'student' => $student,
        ]);
    }
}
