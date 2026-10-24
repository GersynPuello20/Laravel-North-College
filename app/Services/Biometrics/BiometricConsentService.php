<?php

namespace App\Services\Biometrics;

use App\Models\AuditLog;
use App\Models\BiometricConsent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BiometricConsentService
{
    /**
     * Registra una autorización nueva. Si había otra vigente queda
     * sustituida (revocada) para que solo exista una activa.
     */
    public function grant(User $student, array $data, User $actor): BiometricConsent
    {
        $profile = $student->student;
        $isMinor = $profile?->isMinor();

        if ($isMinor === null) {
            throw new InvalidArgumentException('Registra la fecha de nacimiento del estudiante antes de solicitar la autorización.');
        }

        if ($isMinor && $data['granted_by_relationship'] === 'self') {
            throw new InvalidArgumentException('El estudiante es menor de edad: la autorización la debe dar su representante legal.');
        }

        if (! $isMinor && $data['granted_by_relationship'] !== 'self') {
            throw new InvalidArgumentException('El estudiante es mayor de edad: la autorización la debe dar él mismo.');
        }

        if ($isMinor && empty($data['minor_was_heard'])) {
            throw new InvalidArgumentException('Antes de autorizar hay que escuchar la opinión del menor.');
        }

        return DB::transaction(function () use ($student, $data, $actor, $isMinor) {
            $student->biometricConsents()->active()->get()
                ->each(fn (BiometricConsent $previous) => $previous->update([
                    'status' => BiometricConsent::STATUS_REVOKED,
                    'revoked_at' => now(),
                    'revoked_by' => $actor->id,
                    'revocation_reason' => 'Sustituida por una nueva autorización.',
                ]));

            $consent = BiometricConsent::create([
                'student_id' => $student->id,
                'version' => config('biometrics.consent_version'),
                'status' => BiometricConsent::STATUS_GRANTED,
                'is_minor' => $isMinor,
                'granted_by_name' => $data['granted_by_name'],
                'granted_by_document' => $data['granted_by_document'] ?? null,
                'granted_by_relationship' => $data['granted_by_relationship'],
                'minor_was_heard' => $isMinor ? true : null,
                'granted_at' => now(),
                'recorded_by' => $actor->id,
            ]);

            // Un vector previo seguía ligado a la autorización anterior.
            $student->faceTemplate?->update(['biometric_consent_id' => $consent->id]);

            AuditLog::record('biometric.consent_granted', $consent, [
                'student_id' => $student->id,
                'version' => $consent->version,
                'is_minor' => $isMinor,
            ], $actor);

            return $consent;
        });
    }

    /** Revoca la autorización y elimina de inmediato el vector biométrico. */
    public function revoke(BiometricConsent $consent, User $actor, string $reason): void
    {
        if (! $consent->isActive()) {
            return;
        }

        DB::transaction(function () use ($consent, $actor, $reason) {
            $consent->update([
                'status' => BiometricConsent::STATUS_REVOKED,
                'revoked_at' => now(),
                'revoked_by' => $actor->id,
                'revocation_reason' => $reason,
            ]);

            $deleted = $consent->student->faceTemplate()->delete();

            AuditLog::record('biometric.consent_revoked', $consent, [
                'student_id' => $consent->student_id,
                'template_deleted' => (bool) $deleted,
            ], $actor);
        });
    }
}
