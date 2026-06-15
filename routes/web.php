<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\ActiveUserMiddleware;
use App\Http\Middleware\EnsureTeacherOwnsAttendanceRecord;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::aliasMiddleware('role', RoleMiddleware::class);
Route::aliasMiddleware('active', ActiveUserMiddleware::class);
Route::aliasMiddleware('attendance.owner', EnsureTeacherOwnsAttendanceRecord::class);

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Redirect short attendances path to admin attendances index
    Route::redirect('attendances', '/admin/attendances')->middleware('role:admin|superadmin');

    Route::middleware('role:admin|superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'adminDashboard'])->name('dashboard');
        Route::resource('users', UserController::class)->only(['index', 'update']);
        Route::resource('courses', CourseController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('schedules', ScheduleController::class);
        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'destroy']);
        Route::resource('grades', GradeController::class)->except(['show']);
        Route::resource('attendances', AdminAttendanceController::class)->except(['show']);

        Route::get('attendance-reports', [AttendanceReportController::class, 'adminIndex'])->name('attendance.reports.index');
        Route::get('attendance-reports/pdf', [AttendanceReportController::class, 'adminPdf'])->name('attendance.reports.pdf');
        Route::get('attendance-reports/excel', [AttendanceReportController::class, 'adminExcel'])->name('attendance.reports.excel');
    });

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'teacherDashboard'])->name('dashboard');
        Route::get('courses', [CourseController::class, 'teacherIndex'])->name('courses.index');
        Route::get('grades', [GradeController::class, 'teacherIndex'])->name('grades.index');
        Route::get('grades/create', [GradeController::class, 'create'])->name('grades.create');
        Route::post('grades', [GradeController::class, 'store'])->name('grades.store');
        Route::get('grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
        Route::put('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');

        Route::get('attendance', [TeacherAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/create', [TeacherAttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/{attendance}/edit', [TeacherAttendanceController::class, 'edit'])->middleware('attendance.owner')->name('attendance.edit');
        Route::put('attendance/{attendance}', [TeacherAttendanceController::class, 'update'])->middleware('attendance.owner')->name('attendance.update');
        Route::delete('attendance/{attendance}', [TeacherAttendanceController::class, 'destroy'])->middleware('attendance.owner')->name('attendance.destroy');

        Route::get('attendance/report', [AttendanceReportController::class, 'teacherReport'])->name('attendance.report');
        Route::get('attendance/report/pdf', [AttendanceReportController::class, 'teacherPdf'])->name('attendance.report.pdf');
        Route::get('attendance/report/excel', [AttendanceReportController::class, 'teacherExcel'])->name('attendance.report.excel');
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
