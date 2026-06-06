<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\ActiveUserMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::aliasMiddleware('role', RoleMiddleware::class);
Route::aliasMiddleware('active', ActiveUserMiddleware::class);

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('attendances', function () {
        return redirect()->route('admin.attendances.index');
    })->middleware('role:admin|superadmin');

    Route::middleware('role:admin|superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'adminDashboard'])->name('dashboard');
        Route::resource('users', UserController::class)->only(['index', 'update']);
        Route::resource('courses', CourseController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('schedules', ScheduleController::class);
        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'destroy']);
        Route::resource('grades', GradeController::class)->except(['show']);
        Route::resource('attendances', AttendanceController::class)->except(['show']);
    });

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'teacherDashboard'])->name('dashboard');
        Route::get('courses', [CourseController::class, 'teacherIndex'])->name('courses.index');
        Route::get('grades', [GradeController::class, 'teacherIndex'])->name('grades.index');
        Route::get('grades/create', [GradeController::class, 'create'])->name('grades.create');
        Route::post('grades', [GradeController::class, 'store'])->name('grades.store');
        Route::get('grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
        Route::put('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'studentDashboard'])->name('dashboard');
        Route::get('courses', [CourseController::class, 'available'])->name('courses.available');
        Route::get('enrollments', [EnrollmentController::class, 'studentIndex'])->name('enrollments.index');
        Route::get('enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create');
        Route::post('enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
        Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
