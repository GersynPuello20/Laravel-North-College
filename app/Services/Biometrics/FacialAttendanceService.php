<?php

namespace App\Services\Biometrics;

use App\Models\ClassSession;
use App\Models\FaceRecognitionAttempt;
use App\Models\FaceTemplate;
use App\Models\SessionAttendance;
use App\Models\User;
use App\Services\Attendance\AttendanceException;
use App\Services\Attendance\AttendanceService;

/**
 * Identifica al estudiante entre los matriculados en el grupo de la sesión
 * (no entre toda la institución) y, solo si la coincidencia es firme,
 * registra la asistencia con el mismo servicio que el registro manual.
 */
class FacialAttendanceService
{
    public function __construct(
        private FaceEngineClient $engine,
        private FaceMatcher $matcher,
        private AttendanceService $attendance,
    ) {}

    /**
     * @param  list<string>  $frames
     */
    public function recognize(ClassSession $session, array $frames, ?string $direction, User $actor): array
    {
        if (! $session->isOpen()) {
            return $this->result($session, $actor, 'session_closed', 'La sesión no está abierta. Ábrela o usa el registro manual.');
        }

        $requireLiveness = (bool) config('biometrics.liveness_required');
        if ($requireLiveness && ! $direction) {
            return $this->result($session, $actor, 'challenge_invalid', 'El reto de presencia caducó. Vuelve a intentarlo.');
        }

        $candidates = $this->candidates($session);
        if ($candidates === []) {
            return $this->result($session, $actor, 'no_candidates', 'Ningún estudiante de este grupo tiene enrolamiento facial vigente. Usa el registro manual.');
        }

        try {
            $response = $this->engine->embed($frames, $requireLiveness, $direction);
        } catch (FaceEngineUnavailable $error) {
            return $this->result($session, $actor, 'engine_unavailable', $error->getMessage().' Usa el registro manual.');
        }

        if (! ($response['ok'] ?? false)) {
            $code = ($response['error'] ?? '') === 'liveness_failed' ? 'liveness_failed' : 'capture_failed';

            return $this->result($session, $actor, $code, CaptureMessages::for($response), extra: ['engine_error' => $response['error'] ?? null]);
        }

        $match = $this->matcher->match($response['embedding'], $candidates);

        return match ($match['result']) {
            FaceMatcher::MATCHED => $this->register($session, $actor, $match),
            FaceMatcher::AMBIGUOUS => $this->result($session, $actor, 'ambiguous', 'El rostro se parece a más de un estudiante. No se registró asistencia; usa el registro manual.', $match['score']),
            FaceMatcher::LOW_CONFIDENCE => $this->result($session, $actor, 'low_confidence', 'La coincidencia no es suficiente para registrar la asistencia. Repite la captura o usa el registro manual.', $match['score']),
            default => $this->result($session, $actor, 'unknown', 'No se reconoció a ningún estudiante enrolado de este grupo.', $match['score']),
        };
    }

    /** @return array<int, list<float>> */
    private function candidates(ClassSession $session): array
    {
        $rosterIds = $this->attendance->rosterQuery($session)->pluck('users.id');

        return FaceTemplate::whereIn('student_id', $rosterIds)
            ->whereHas('consent', fn ($consent) => $consent->active())
            ->get()
            ->mapWithKeys(fn (FaceTemplate $template) => [$template->student_id => $template->embedding])
            ->all();
    }

    private function register(ClassSession $session, User $actor, array $match): array
    {
        $student = User::find($match['student_id']);

        try {
            $record = $this->attendance->mark($session, $student, $actor, SessionAttendance::METHOD_FACIAL, score: $match['score']);
        } catch (AttendanceException $error) {
            if ($error->reason === 'already_registered') {
                $existing = $session->attendances()->where('student_id', $student->id)->first();

                return $this->result($session, $actor, 'already_registered', "{$student->name} ya tenía asistencia registrada en esta sesión.", $match['score'], $student, [
                    'student' => ['name' => $student->name],
                    'status' => $existing?->statusLabel(),
                ]);
            }

            return $this->result($session, $actor, $error->reason, $error->getMessage(), $match['score'], $student);
        }

        return $this->result($session, $actor, 'matched', "Asistencia registrada: {$student->name}.", $match['score'], $student, [
            'student' => ['name' => $student->name],
            'status' => $record->statusLabel(),
            'recorded_at' => $record->recorded_at->format('H:i'),
        ]);
    }

    private function result(ClassSession $session, User $actor, string $code, string $message, ?float $score = null, ?User $student = null, array $extra = []): array
    {
        FaceRecognitionAttempt::create([
            'class_session_id' => $session->id,
            'purpose' => FaceRecognitionAttempt::PURPOSE_ATTENDANCE,
            'result' => $code,
            'student_id' => $student?->id,
            'score' => $score,
            'performed_by' => $actor->id,
        ]);

        return array_merge([
            'ok' => in_array($code, ['matched', 'already_registered'], true),
            'code' => $code,
            'message' => $message,
            'score' => $score,
        ], $extra);
    }
}
