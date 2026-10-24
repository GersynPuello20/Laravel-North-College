<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ClassSession extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_LABELS = [
        self::STATUS_SCHEDULED => 'Programada',
        self::STATUS_OPEN => 'Abierta',
        self::STATUS_CLOSED => 'Cerrada',
    ];

    protected $fillable = [
        'course_id',
        'schedule_id',
        'session_date',
        'starts_at',
        'ends_at',
        'late_after_minutes',
        'topic',
        'status',
        'opened_at',
        'opened_by',
        'closed_at',
        'closed_by',
        'created_by',
    ];

    protected $attributes = [
        'status' => self::STATUS_SCHEDULED,
        'late_after_minutes' => 10,
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'late_after_minutes' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(SessionAttendance::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function lateAfter(): Carbon
    {
        return $this->starts_at->copy()->addMinutes($this->late_after_minutes);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->whereHas('course', fn ($course) => $course->where('professor_id', $teacherId));
    }
}
