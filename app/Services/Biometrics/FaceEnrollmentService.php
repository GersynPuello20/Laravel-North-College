<?php

namespace App\Services\Biometrics;

use App\Models\AuditLog;
use App\Models\FaceRecognitionAttempt;
use App\Models\FaceTemplate;
use App\Models\User;

class FaceEnrollmentService
{
    public function __construct(
        private FaceEngineClient $engine,
        private FaceMatcher $matcher,
    ) {}

    /**
     * @param  list<string>  $frames
     * @return array{ok: bool, code: string, message: string, quality?: float}
     */
    public function enroll(User $student, array $frames, ?string $direction, User $actor): array
    {
        if (! $student->isStudent() || ! $student->isActive()) {
            return $this->fail($student, $actor, 'capture_failed', 'El estudiante no existe o no está activo.');
        }

        $consent = $student->activeBiometricConsent;
        if (! $consent) {
            return $this->fail($student, $actor, 'capture_failed', 'El estudiante no tiene una autorización biométrica vigente.', log: false);
        }

        $requireLiveness = (bool) config('biometrics.liveness_required');
        if ($requireLiveness && ! $direction) {
            return $this->fail($student, $actor, 'challenge_invalid', 'El reto de presencia caducó. Vuelve a intentarlo.');
        }

        try {
            $response = $this->engine->embed($frames, $requireLiveness, $direction);
        } catch (FaceEngineUnavailable $error) {
            return $this->fail($student, $actor, 'engine_unavailable', $error->getMessage());
        }

        if (! ($response['ok'] ?? false)) {
            $code = ($response['error'] ?? '') === 'liveness_failed' ? 'liveness_failed' : 'capture_failed';

            return $this->fail($student, $actor, $code, CaptureMessages::for($response), engineError: $response['error'] ?? null);
        }

        $embedding = $response['embedding'];

        // Si el rostro ya pertenece a otro estudiante, no se enrola.
        $others = FaceTemplate::where('student_id', '<>', $student->id)->get()
            ->mapWithKeys(fn (FaceTemplate $template) => [$template->student_id => $template->embedding])
            ->all();

        if ($others !== []) {
            $match = $this->matcher->match($embedding, $others);
            if (in_array($match['result'], [FaceMatcher::MATCHED, FaceMatcher::AMBIGUOUS], true)) {
                return $this->fail($student, $actor, 'duplicate_identity', 'Este rostro coincide con otro estudiante ya enrolado. Revisa la identidad antes de continuar.', $match['score']);
            }
        }

        $template = FaceTemplate::updateOrCreate(
            ['student_id' => $student->id],
            [
                'biometric_consent_id' => $consent->id,
                'embedding' => $embedding,
                'engine' => $response['engine'],
                'quality' => $response['quality'] ?? null,
                'enrolled_by' => $actor->id,
            ]
        );

        FaceRecognitionAttempt::create([
            'purpose' => FaceRecognitionAttempt::PURPOSE_ENROLLMENT,
            'result' => 'enrolled',
            'student_id' => $student->id,
            'performed_by' => $actor->id,
        ]);

        AuditLog::record($template->wasRecentlyCreated ? 'biometric.enrolled' : 'biometric.re_enrolled', $template, [
            'student_id' => $student->id,
            'engine' => $template->engine,
            'liveness' => $response['liveness']['passed'] ?? false,
        ], $actor);

        return [
            'ok' => true,
            'code' => 'enrolled',
            'message' => 'Rostro enrolado correctamente.',
            'quality' => $template->quality,
        ];
    }

    public function delete(User $student, User $actor, string $reason): bool
    {
        $template = $student->faceTemplate;
        if (! $template) {
            return false;
        }

        $template->delete();
        AuditLog::record('biometric.template_deleted', $student, ['reason' => $reason], $actor);

        return true;
    }

    private function fail(User $student, User $actor, string $code, string $message, ?float $score = null, bool $log = true, ?string $engineError = null): array
    {
        if ($log) {
            FaceRecognitionAttempt::create([
                'purpose' => FaceRecognitionAttempt::PURPOSE_ENROLLMENT,
                'result' => $code,
                'student_id' => $student->id,
                'score' => $score,
                'performed_by' => $actor->id,
            ]);
        }

        return ['ok' => false, 'code' => $engineError ?? $code, 'message' => $message];
    }
}
