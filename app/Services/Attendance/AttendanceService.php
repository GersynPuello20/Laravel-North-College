<?php

namespace App\Services\Attendance;

use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\SessionAttendance;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reglas del módulo de asistencia. Es independiente del método de registro:
 * el reconocimiento facial usa este mismo servicio con METHOD_FACIAL.
 */
class AttendanceService
{
    public function open(ClassSession $session, User $actor): ClassSession
    {
        if (! $session->isScheduled()) {
            throw AttendanceException::invalidTransition('Solo se puede abrir una sesión programada.');
        }

        $session->update([
            'status' => ClassSession::STATUS_OPEN,
            'opened_at' => now(),
            'opened_by' => $actor->id,
        ]);

        AuditLog::record('session.opened', $session, actor: $actor);

        return $session;
    }

    /**
     * Cierra la sesión y marca como ausentes a los matriculados sin registro.
     * La inasistencia sale de esta regla, nunca de un rostro no reconocido.
     */
    public function close(ClassSession $session, User $actor): int
    {
        if (! $session->isOpen()) {
            throw AttendanceException::invalidTransition('Solo se puede cerrar una sesión abierta.');
        }

        return DB::transaction(function () use ($session, $actor) {
            $registered = $session->attendances()->pluck('student_id');
            $missing = $this->rosterQuery($session)->whereNotIn('users.id', $registered)->pluck('users.id');
            $now = now();

            foreach ($missing as $studentId) {
                SessionAttendance::create([
                    'class_session_id' => $session->id,
                    'student_id' => $studentId,
                    'status' => SessionAttendance::STATUS_ABSENT,
                    'method' => SessionAttendance::METHOD_SESSION_CLOSE,
                    'recorded_at' => $now,
                    'recorded_by' => $actor->id,
                ]);
            }

            $session->update([
                'status' => ClassSession::STATUS_CLOSED,
                'closed_at' => $now,
                'closed_by' => $actor->id,
            ]);

            AuditLog::record('session.closed', $session, ['absent_marked' => $missing->count()], $actor);

            return $missing->count();
        });
    }

    /**
     * Registra la asistencia de un estudiante. Si no se indica estado, se
     * calcula con la hora: presente o tarde según el margen de la sesión.
     */
    public function mark(
        ClassSession $session,
        User $student,
        User $actor,
        string $method = SessionAttendance::METHOD_MANUAL,
        ?string $status = null,
        ?float $score = null,
        ?string $note = null,
    ): SessionAttendance {
        if (! $session->isOpen()) {
            throw AttendanceException::sessionNotOpen();
        }

        if (! $this->isEnrolled($session, $student)) {
            throw AttendanceException::notEnrolled();
        }

        $now = now();
        $status ??= $this->statusForArrival($session, $now);

        try {
            return DB::transaction(function () use ($session, $student, $actor, $method, $status, $score, $note, $now) {
                if ($session->attendances()->where('student_id', $student->id)->exists()) {
                    throw AttendanceException::alreadyRegistered();
                }

                return SessionAttendance::create([
                    'class_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => $status,
                    'method' => $method,
                    'recorded_at' => $now,
                    'recorded_by' => $actor->id,
                    'match_score' => $score,
                    'note' => $note,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            // Dos registros simultáneos: la restricción única de la base de datos gana.
            throw AttendanceException::alreadyRegistered();
        }
    }

    /** Cambia el estado de un registro existente dejando motivo y responsable. */
    public function correct(SessionAttendance $attendance, string $newStatus, string $reason, User $actor): SessionAttendance
    {
        if ($attendance->status === $newStatus) {
            throw AttendanceException::invalidTransition('El registro ya tiene ese estado.');
        }

        return DB::transaction(function () use ($attendance, $newStatus, $reason, $actor) {
            $oldStatus = $attendance->status;

            AttendanceCorrection::create([
                'session_attendance_id' => $attendance->id,
                'changed_by' => $actor->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
            ]);

            $attendance->update([
                'status' => $newStatus,
                'needs_review' => false,
            ]);

            AuditLog::record('attendance.corrected', $attendance, [
                'from' => $oldStatus,
                'to' => $newStatus,
            ], $actor);

            return $attendance;
        });
    }

    public function statusForArrival(ClassSession $session, Carbon $at): string
    {
        return $at->greaterThan($session->lateAfter())
            ? SessionAttendance::STATUS_LATE
            : SessionAttendance::STATUS_PRESENT;
    }

    public function isEnrolled(ClassSession $session, User $student): bool
    {
        return $this->rosterQuery($session)->where('users.id', $student->id)->exists();
    }

    /** Estudiantes que cuentan para la sesión: matrícula y cuenta activas. */
    public function rosterQuery(ClassSession $session)
    {
        return $session->course->activeStudents()->getQuery()->select('users.*');
    }

    public function roster(ClassSession $session): Collection
    {
        return $this->rosterQuery($session)->orderBy('users.name')->get();
    }

    /**
     * Crea las sesiones del día a partir de los horarios del grupo. Las que ya
     * existen (mismo grupo y hora de inicio) no se duplican.
     */
    public function createSessionsFromSchedules(Course $course, Carbon $date, User $actor): Collection
    {
        $isoDay = $date->dayOfWeekIso;

        return $course->schedules
            ->filter(fn (Schedule $schedule) => $schedule->isoDay() === $isoDay)
            ->map(function (Schedule $schedule) use ($course, $date, $actor) {
                $startsAt = $date->copy()->setTimeFromTimeString($schedule->start_time->format('H:i'));
                $endsAt = $date->copy()->setTimeFromTimeString($schedule->end_time->format('H:i'));

                return ClassSession::firstOrCreate(
                    ['course_id' => $course->id, 'starts_at' => $startsAt],
                    [
                        'schedule_id' => $schedule->id,
                        'session_date' => $date->toDateString(),
                        'ends_at' => $endsAt,
                        'created_by' => $actor->id,
                    ]
                );
            })
            ->values();
    }
}
