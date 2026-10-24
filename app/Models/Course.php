<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Subject;
use App\Models\Schedule;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'professor_id',
        'status',
        'created_by',
        'updated_by',
    ];

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'student_id')
            ->withTimestamps();
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** Estudiantes con matrícula activa y cuenta activa: los que cuentan para la asistencia. */
    public function activeStudents(): BelongsToMany
    {
        return $this->students()
            ->wherePivot('status', 'active')
            ->where('users.status', User::STATUS_ACTIVE);
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
