<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('dashboards.admin.index', [
            'user' => $user,
        ]);
    }
}
