<?php

namespace App\Services\Attendance;

use App\Models\FaceRecognitionAttempt;
use App\Models\SessionAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Consultas de los reportes. Todas las cifras salen de session_attendances
 * y face_recognition_attempts; un tutor solo ve sus grupos.
 */
class AttendanceReport
{
    public function __construct(private User $viewer, private array $filters)
    {
        $this->filters['from'] = Carbon::parse($filters['from'] ?? now()->subDays(29))->startOfDay();
        $this->filters['to'] = Carbon::parse($filters['to'] ?? now())->endOfDay();
    }

    public function from(): Carbon
    {
        return $this->filters['from'];
    }

    public function to(): Carbon
    {
        return $this->filters['to'];
    }

    public function query(): Builder
    {
        $f = $this->filters;

        return SessionAttendance::query()
            ->whereHas('classSession', function (Builder $session) use ($f) {
                $session->whereDate('session_date', '>=', $f['from']->toDateString())
                    ->whereDate('session_date', '<=', $f['to']->toDateString());

                if (! $this->viewer->isStaff()) {
                    $session->whereHas('course', fn ($c) => $c->where('professor_id', $this->viewer->id));
                }
                if (! empty($f['course_id'])) {
                    $session->where('course_id', $f['course_id']);
                }
                if (! empty($f['teacher_id']) && $this->viewer->isStaff()) {
                    $session->whereHas('course', fn ($c) => $c->where('professor_id', $f['teacher_id']));
                }
            })
            ->when($f['status'] ?? null, fn ($q, $status) => $q->where('session_attendances.status', $status))
            ->when($f['method'] ?? null, fn ($q, $method) => $q->where('session_attendances.method', $method))
            ->when(! empty($f['needs_review']), fn ($q) => $q->where('session_attendances.needs_review', true))
            ->when(! empty($f['corrected']), fn ($q) => $q->has('corrections'))
            ->when($f['student'] ?? null, fn ($q, $term) => $q->whereHas('student', fn ($s) => $s->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")));
    }

    public function records(): Builder
    {
        return $this->query()
            ->with(['student', 'classSession.course.professor', 'recordedBy', 'corrections.changedBy'])
            ->join('class_sessions', 'class_sessions.id', '=', 'session_attendances.class_session_id')
            ->orderByDesc('class_sessions.starts_at')
            ->orderBy('session_attendances.id')
            ->select('session_attendances.*');
    }

    /** @return Collection<string, int> conteo por estado */
    public function totals(): Collection
    {
        $counts = $this->query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return collect(SessionAttendance::STATUS_LABELS)->map(fn ($label, $status) => (int) ($counts[$status] ?? 0));
    }

    public function attendanceRate(?Collection $totals = null): ?float
    {
        $totals ??= $this->totals();
        $all = $totals->sum();

        if ($all === 0) {
            return null;
        }

        return round(($totals[SessionAttendance::STATUS_PRESENT] + $totals[SessionAttendance::STATUS_LATE]) * 100 / $all, 1);
    }

    /** Intentos fallidos de reconocimiento en el periodo, agrupados por causa. */
    public function facialFailures(): Collection
    {
        $f = $this->filters;

        return FaceRecognitionAttempt::query()
            ->where('purpose', FaceRecognitionAttempt::PURPOSE_ATTENDANCE)
            ->whereNotIn('result', ['matched', 'already_registered'])
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->whereHas('classSession', function (Builder $session) use ($f) {
                if (! $this->viewer->isStaff()) {
                    $session->whereHas('course', fn ($c) => $c->where('professor_id', $this->viewer->id));
                }
                if (! empty($f['course_id'])) {
                    $session->where('course_id', $f['course_id']);
                }
            })
            ->selectRaw('result, COUNT(*) as total')
            ->groupBy('result')
            ->orderByDesc('total')
            ->pluck('total', 'result');
    }
}
