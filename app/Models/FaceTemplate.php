<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vector biométrico de un estudiante. Es un dato sensible: se guarda cifrado
 * con APP_KEY, nunca se serializa a JSON y solo lo leen los servicios de
 * enrolamiento y reconocimiento.
 */
class FaceTemplate extends Model
{
    protected $fillable = [
        'student_id',
        'biometric_consent_id',
        'embedding',
        'engine',
        'quality',
        'enrolled_by',
    ];

    protected $hidden = [
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'encrypted:array',
            'quality' => 'float',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function consent(): BelongsTo
    {
        return $this->belongsTo(BiometricConsent::class, 'biometric_consent_id');
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }
}
