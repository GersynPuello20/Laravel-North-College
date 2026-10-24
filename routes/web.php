<?php

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Attendance\ClassSessionController;
use App\Http\Controllers\Attendance\ReportController;
use App\Http\Controllers\Attendance\SessionAttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Facial\AttendanceController as FacialAttendanceController;
use App\Http\Controllers\Facial\BiometricController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Student\MyAttendanceController;
use App\Http\Middleware\ActiveUserMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::aliasMiddleware('role', RoleMiddleware::class);
Route::aliasMiddleware('active', ActiveUserMiddleware::class);

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Asistencia: personal académico y tutores (cada tutor solo ve sus grupos).
    Route::middleware('role:admin|coordinator|teacher')->group(function () {
        Route::get('sessions', [ClassSessionController::class, 'index'])->name('sessions.index');
        Route::get('sessions/create', [ClassSessionController::class, 'create'])->name('sessions.create');
        Route::post('sessions', [ClassSessionController::class, 'store'])->name('sessions.store');
        Route::post('sessions/generate', [ClassSessionController::class, 'generate'])->name('sessions.generate');
        Route::get('sessions/{classSession}', [ClassSessionController::class, 'show'])->name('sessions.show');
        Route::post('sessions/{classSession}/open', [ClassSessionController::class, 'open'])->name('sessions.open');
        Route::post('sessions/{classSession}/close', [ClassSessionController::class, 'close'])->name('sessions.close');
        Route::delete('sessions/{classSession}', [ClassSessionController::class, 'destroy'])->name('sessions.destroy');

        Route::post('sessions/{classSession}/attendances', [SessionAttendanceController::class, 'store'])->name('sessions.attendances.store');
        Route::put('attendances/{attendance}', [SessionAttendanceController::class, 'update'])->name('attendances.update');

        Route::get('sessions/{classSession}/facial', [FacialAttendanceController::class, 'show'])->name('sessions.facial');
        Route::middleware('throttle:biometrics')->group(function () {
            Route::post('sessions/{classSession}/facial/challenge', [FacialAttendanceController::class, 'challenge'])->name('sessions.facial.challenge');
            Route::post('sessions/{classSession}/facial/recognize', [FacialAttendanceController::class, 'recognize'])->name('sessions.facial.recognize');
        });
        Route::post('sessions/{classSession}/facial/detect', [FacialAttendanceController::class, 'detect'])
            ->middleware('throttle:biometrics-detect')
            ->name('sessions.facial.detect');

        Route::get('reports/attendance', [ReportController::class, 'index'])->name('reports.attendance');
        Route::get('reports/attendance/export', [ReportController::class, 'export'])->name('reports.attendance.export');
    });

    // Estudiantes y datos biométricos: solo personal académico.
    Route::middleware('role:admin|coordinator')->group(function () {
        Route::get('students', [StudentController::class, 'index'])->name('students.index');
        Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');

        Route::get('students/{student}/biometrics', [BiometricController::class, 'show'])->name('students.biometrics.show');
        Route::post('students/{student}/biometrics/consent', [BiometricController::class, 'storeConsent'])->name('students.biometrics.consent');
        Route::post('students/{student}/biometrics/revoke', [BiometricController::class, 'revokeConsent'])->name('students.biometrics.revoke');
        Route::delete('students/{student}/biometrics/template', [BiometricController::class, 'destroyTemplate'])->name('students.biometrics.destroy');
        Route::middleware('throttle:biometrics')->group(function () {
            Route::post('students/{student}/biometrics/challenge', [BiometricController::class, 'challenge'])->name('students.biometrics.challenge');
            Route::post('students/{student}/biometrics/enroll', [BiometricController::class, 'enroll'])->name('students.biometrics.enroll');
        });
        Route::post('students/{student}/biometrics/detect', [BiometricController::class, 'detect'])
            ->middleware('throttle:biometrics-detect')
            ->name('students.biometrics.detect');

        Route::get('settings', SettingsController::class)->name('settings');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'update']);
        Route::resource('courses', CourseController::class)->except(['show']);
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('schedules', ScheduleController::class)->except(['show']);
        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'destroy']);
        Route::post('enrollments', [EnrollmentController::class, 'adminStore'])->name('enrollments.store');
        Route::resource('grades', GradeController::class)->only(['index', 'destroy']);
    });

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('courses', [CourseController::class, 'teacherIndex'])->name('courses.index');
        Route::get('grades', [GradeController::class, 'teacherIndex'])->name('grades.index');
        Route::get('grades/create', [GradeController::class, 'create'])->name('grades.create');
        Route::post('grades', [GradeController::class, 'store'])->name('grades.store');
        Route::get('grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
        Route::put('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('attendance', MyAttendanceController::class)->name('attendance');
        Route::get('biometrics', [BiometricController::class, 'mine'])->name('biometrics');
        Route::post('biometrics/revoke', [BiometricController::class, 'revokeMine'])->name('biometrics.revoke');
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
