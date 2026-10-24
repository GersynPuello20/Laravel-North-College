<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'session_attendance_id',
        'changed_by',
        'old_status',
        'new_status',
        'reason',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(SessionAttendance::class, 'session_attendance_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
