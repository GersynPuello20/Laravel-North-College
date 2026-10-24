<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Resultado de cada intento de enrolamiento o reconocimiento. Solo guarda lo
 * necesario para justificar la decisión: nunca imágenes ni vectores.
 */
class FaceRecognitionAttempt extends Model
{
    public const UPDATED_AT = null;

    public const PURPOSE_ENROLLMENT = 'enrollment';

    public const PURPOSE_ATTENDANCE = 'attendance';

    public const RESULT_LABELS = [
        'matched' => 'Reconocido y registrado',
        'already_registered' => 'Ya tenía asistencia',
        'enrolled' => 'Enrolado',
        'low_confidence' => 'Baja confianza',
        'ambiguous' => 'Coincidencia ambigua',
        'unknown' => 'Rostro desconocido',
        'no_candidates' => 'Sin estudiantes enrolados',
        'duplicate_identity' => 'Coincide con otro estudiante',
        'liveness_failed' => 'Falló la prueba de presencia',
        'challenge_invalid' => 'Reto caducado o no válido',
        'capture_failed' => 'Captura no válida',
        'session_closed' => 'Sesión no abierta',
        'engine_unavailable' => 'Motor no disponible',
    ];

    protected $fillable = [
        'class_session_id',
        'purpose',
        'result',
        'student_id',
        'score',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
        ];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function resultLabel(): string
    {
        return self::RESULT_LABELS[$this->result] ?? $this->result;
    }
}
