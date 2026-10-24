<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BiometricConsent extends Model
{
    public const STATUS_GRANTED = 'granted';

    public const STATUS_REVOKED = 'revoked';

    public const RELATIONSHIPS = [
        'self' => 'El mismo estudiante (mayor de edad)',
        'mother' => 'Madre',
        'father' => 'Padre',
        'legal_guardian' => 'Representante legal o tutor',
    ];

    protected $fillable = [
        'student_id',
        'version',
        'status',
        'is_minor',
        'granted_by_name',
        'granted_by_document',
        'granted_by_relationship',
        'minor_was_heard',
        'granted_at',
        'recorded_by',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_minor' => 'boolean',
            'minor_was_heard' => 'boolean',
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function faceTemplate(): HasOne
    {
        return $this->hasOne(FaceTemplate::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_GRANTED;
    }

    public function relationshipLabel(): string
    {
        return self::RELATIONSHIPS[$this->granted_by_relationship] ?? $this->granted_by_relationship;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_GRANTED);
    }
}
