<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionAttendance extends Model
{
    use HasFactory;

    public const STATUS_PRESENT = 'present';

    public const STATUS_LATE = 'late';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_EXCUSED = 'excused';

    public const STATUS_LABELS = [
        self::STATUS_PRESENT => 'Presente',
        self::STATUS_LATE => 'Tarde',
        self::STATUS_ABSENT => 'Ausente',
        self::STATUS_EXCUSED => 'Ausencia justificada',
    ];

    public const METHOD_MANUAL = 'manual';

    public const METHOD_FACIAL = 'facial';

    public const METHOD_SESSION_CLOSE = 'session_close';

    public const METHOD_LABELS = [
        self::METHOD_MANUAL => 'Manual',
        self::METHOD_FACIAL => 'Reconocimiento facial',
        self::METHOD_SESSION_CLOSE => 'Cierre de sesión',
    ];

    protected $fillable = [
        'class_session_id',
        'student_id',
        'status',
        'method',
        'recorded_at',
        'recorded_by',
        'match_score',
        'needs_review',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'match_score' => 'float',
            'needs_review' => 'boolean',
        ];
    }

    public static function statuses(): array
    {
        return array_keys(self::STATUS_LABELS);
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class)->latest('created_at');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function methodLabel(): string
    {
        return self::METHOD_LABELS[$this->method] ?? $this->method;
    }
}
