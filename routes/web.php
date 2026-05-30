<?php

use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware(['auth'])->name('dashboard');



Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'update']);
    Route::resource('students', StudentController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('tutors', \App\Http\Controllers\Admin\TutorController::class)->only(['index']);
    Route::resource('attendances', \App\Http\Controllers\Admin\AttendanceController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
