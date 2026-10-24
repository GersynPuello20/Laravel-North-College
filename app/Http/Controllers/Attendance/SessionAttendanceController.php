<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\SessionAttendance;
use App\Models\User;
use App\Services\Attendance\AttendanceException;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SessionAttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance) {}

    /**
     * Registro manual. Recibe marks[student_id] = estado; "auto" calcula
     * presente o tarde según la hora. Sirve para uno o varios estudiantes.
     */
    public function store(Request $request, ClassSession $classSession): RedirectResponse
    {
        Gate::authorize('manage', $classSession);

        $data = $request->validate([
            'marks' => ['required', 'array', 'min:1'],
            'marks.*' => ['nullable', Rule::in(array_merge(['auto'], SessionAttendance::statuses()))],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $marks = collect($data['marks'])->filter();
        if ($marks->isEmpty()) {
            return back()->with('error', 'Selecciona al menos un estado para registrar.');
        }

        $students = User::whereIn('id', $marks->keys())->get()->keyBy('id');
        $saved = 0;
        $errors = [];

        foreach ($marks as $studentId => $status) {
            $student = $students->get($studentId);
            if (! $student) {
                continue;
            }

            try {
                $this->attendance->mark(
                    $classSession,
                    $student,
                    $request->user(),
                    SessionAttendance::METHOD_MANUAL,
                    $status === 'auto' ? null : $status,
                    note: $data['note'] ?? null,
                );
                $saved++;
            } catch (AttendanceException $error) {
                $errors[] = "{$student->name}: {$error->getMessage()}";
            }
        }

        $redirect = back();
        if ($saved > 0) {
            $redirect->with('success', $saved === 1 ? 'Asistencia registrada.' : "Se registraron {$saved} asistencias.");
        }
        if ($errors !== []) {
            $redirect->with('error', implode(' ', $errors));
        }

        return $redirect;
    }

    /** Corrección de un registro existente: exige motivo y queda en el historial. */
    public function update(Request $request, SessionAttendance $attendance): RedirectResponse
    {
        Gate::authorize('update', $attendance);

        $data = $request->validate([
            'status' => ['required', Rule::in(SessionAttendance::statuses())],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $this->attendance->correct($attendance, $data['status'], $data['reason'], $request->user());
        } catch (AttendanceException $error) {
            return back()->with('error', $error->getMessage());
        }

        return back()->with('success', 'Registro corregido. El cambio quedó en el historial.');
    }
}
