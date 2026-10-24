<?php

namespace App\Services\Attendance;

use App\Models\AttendanceCorrection;
use App\Models\ClassSession;
use App\Models\FaceRecognitionAttempt;
use App\Models\FaceTemplate;
use App\Models\SessionAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Indicadores del panel. Todo sale de la base de datos; si no hay datos se
 * devuelve null o una colección vacía y la vista muestra un estado vacío.
 */
class DashboardStats
{
    public function __construct(private User $viewer) {}

    public function sessions(): Builder
    {
        return $this->viewer->isStaff() ? ClassSession::query() : ClassSession::forTeacher($this->viewer->id);
    }

    public function attendances(): Builder
    {
        return SessionAttendance::query()->whereIn('class_session_id', $this->sessions()->select('id'));
    }

    public function activeStudents(): int
    {
        $query = User::students()->active();

        if (! $this->viewer->isStaff()) {
            $query->whereHas('enrollments', fn ($e) => $e->where('status', 'active')
                ->whereHas('course', fn ($c) => $c->where('professor_id', $this->viewer->id)));
        }

        return $query->count();
    }

    /** @return Collection<string, int> estado => total de hoy */
    public function today(): Collection
    {
        $counts = $this->attendances()
            ->whereHas('classSession', fn ($s) => $s->whereDate('session_date', today()))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(SessionAttendance::STATUS_LABELS)->map(fn ($label, $status) => (int) ($counts[$status] ?? 0));
    }

    /** Porcentaje de asistencia (presente + tarde) en sesiones cerradas de los últimos días. */
    public function rate(int $days = 30): ?float
    {
        $counts = $this->attendances()
            ->whereHas('classSession', fn ($s) => $s->where('status', ClassSession::STATUS_CLOSED)
                ->whereDate('session_date', '>=', today()->subDays($days - 1)))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $all = $counts->sum();
        if ($all === 0) {
            return null;
        }

        $attended = ($counts[SessionAttendance::STATUS_PRESENT] ?? 0) + ($counts[SessionAttendance::STATUS_LATE] ?? 0);

        return round($attended * 100 / $all, 1);
    }

    public function todaySessions(): Collection
    {
        return $this->sessions()
            ->with('course')
            ->withCount([
                'attendances',
                'attendances as attended_count' => fn ($q) => $q->whereIn('status', [SessionAttendance::STATUS_PRESENT, SessionAttendance::STATUS_LATE]),
            ])
            ->whereDate('session_date', today())
            ->orderBy('starts_at')
            ->get()
            ->each(fn (ClassSession $session) => $session->setAttribute('roster_count', $session->course?->activeStudents()->count() ?? 0));
    }

    /** Sesiones que ya terminaron pero siguen abiertas o sin abrir. */
    public function unclosedSessions(): int
    {
        return $this->sessions()
            ->where('status', '<>', ClassSession::STATUS_CLOSED)
            ->where('ends_at', '<', now())
            ->count();
    }

    public function needsReview(): int
    {
        return $this->attendances()->where('needs_review', true)->count();
    }

    public function biometricCoverage(): array
    {
        $students = $this->activeStudents();
        $enrolled = FaceTemplate::whereHas('student', fn ($q) => $q->where('status', User::STATUS_ACTIVE))
            ->whereHas('consent', fn ($q) => $q->active())
            ->when(! $this->viewer->isStaff(), fn ($q) => $q->whereHas('student.enrollments.course', fn ($c) => $c->where('professor_id', $this->viewer->id)))
            ->count();

        return ['students' => $students, 'enrolled' => $enrolled];
    }

    public function facialFailures(int $days = 7): int
    {
        return FaceRecognitionAttempt::query()
            ->where('purpose', FaceRecognitionAttempt::PURPOSE_ATTENDANCE)
            ->whereNotIn('result', ['matched', 'already_registered'])
            ->where('created_at', '>=', now()->subDays($days))
            ->whereIn('class_session_id', $this->sessions()->select('id'))
            ->count();
    }

    /** Últimos registros y correcciones mezclados por fecha. */
    public function recentActivity(int $limit = 8): Collection
    {
        $records = $this->attendances()
            ->with(['student', 'classSession.course', 'recordedBy'])
            ->where('method', '<>', SessionAttendance::METHOD_SESSION_CLOSE)
            ->latest('recorded_at')
            ->limit($limit)
            ->get()
            ->map(fn (SessionAttendance $a) => [
                'type' => 'record',
                'at' => $a->recorded_at,
                'title' => "{$a->student?->name} · {$a->statusLabel()}",
                'detail' => "{$a->classSession->course?->name} · {$a->methodLabel()}",
                'tone' => $a->status,
            ]);

        $corrections = AttendanceCorrection::query()
            ->with(['attendance.student', 'attendance.classSession.course', 'changedBy'])
            ->whereIn('session_attendance_id', $this->attendances()->select('id'))
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (AttendanceCorrection $c) => [
                'type' => 'correction',
                'at' => $c->created_at,
                'title' => "Corrección: {$c->attendance?->student?->name}",
                'detail' => (SessionAttendance::STATUS_LABELS[$c->old_status] ?? $c->old_status).' → '.(SessionAttendance::STATUS_LABELS[$c->new_status] ?? $c->new_status)." · {$c->changedBy?->name}",
                'tone' => 'correction',
            ]);

        return $records->concat($corrections)->sortByDesc('at')->take($limit)->values();
    }
}
