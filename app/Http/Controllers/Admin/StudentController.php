<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Estudiantes = usuarios con rol estudiante. La cuenta se crea por registro
 * o desde Usuarios; aquí se completa el perfil académico.
 */
class StudentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-students');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'course_id' => ['nullable', 'integer'],
            'biometric' => ['nullable', Rule::in(['enrolled', 'consent', 'none'])],
            'status' => ['nullable', Rule::in(array_keys(User::STATUS_LABELS))],
        ]);

        $students = User::students()
            ->with(['student', 'faceTemplate:id,student_id', 'activeBiometricConsent'])
            ->withCount(['enrollments as active_enrollments_count' => fn ($q) => $q->where('status', 'active')])
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('student', fn ($s) => $s->where('enrollment_number', 'like', "%{$term}%")->orWhere('document_number', 'like', "%{$term}%")));
            })
            ->when($filters['course_id'] ?? null, fn ($q, $id) => $q->whereHas('enrollments', fn ($e) => $e->where('course_id', $id)))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when(($filters['biometric'] ?? null) === 'enrolled', fn ($q) => $q->has('faceTemplate'))
            ->when(($filters['biometric'] ?? null) === 'consent', fn ($q) => $q->whereHas('biometricConsents', fn ($c) => $c->active())->doesntHave('faceTemplate'))
            ->when(($filters['biometric'] ?? null) === 'none', fn ($q) => $q->whereDoesntHave('biometricConsents', fn ($c) => $c->active()))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('students.index', [
            'students' => $students,
            'courses' => Course::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function show(User $student): View
    {
        Gate::authorize('manage-students');
        abort_unless($student->isStudent(), 404);

        $student->load(['student', 'enrollments.course.professor', 'faceTemplate', 'activeBiometricConsent']);

        $records = $student->sessionAttendances()
            ->with(['classSession.course', 'recordedBy'])
            ->latest('recorded_at')
            ->paginate(15);

        $totals = $student->sessionAttendances()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counted = $totals->sum();
        $attended = ($totals[SessionAttendance::STATUS_PRESENT] ?? 0) + ($totals[SessionAttendance::STATUS_LATE] ?? 0);

        return view('students.show', [
            'student' => $student,
            'profile' => $student->student,
            'records' => $records,
            'totals' => $totals,
            'rate' => $counted > 0 ? round($attended * 100 / $counted, 1) : null,
        ]);
    }

    public function edit(User $student): View
    {
        Gate::authorize('manage-students');
        abort_unless($student->isStudent(), 404);

        return view('students.edit', [
            'student' => $student,
            'profile' => $student->student ?? new Student,
        ]);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        Gate::authorize('manage-students');
        abort_unless($student->isStudent(), 404);

        $profileId = $student->student?->id;

        $data = $request->validate([
            'enrollment_number' => ['nullable', 'string', 'max:30', Rule::unique('students', 'enrollment_number')->ignore($profileId)],
            'document_number' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_document' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $profile = Student::updateOrCreate(['user_id' => $student->id], $data);

        AuditLog::record('student.profile_updated', $profile, ['fields' => array_keys($profile->getChanges())]);

        return redirect()->route('students.show', $student)->with('success', 'Perfil del estudiante actualizado.');
    }
}
