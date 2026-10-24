<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_ACTIVE => 'Activo',
        self::STATUS_SUSPENDED => 'Suspendido',
    ];

    /** Roles con acceso a la gestión de asistencia y biometría de todos los grupos. */
    public const STAFF_ROLES = ['superadmin', 'admin', 'coordinator'];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function sessionAttendances(): HasMany
    {
        return $this->hasMany(SessionAttendance::class, 'student_id');
    }

    public function biometricConsents(): HasMany
    {
        return $this->hasMany(BiometricConsent::class, 'student_id')->latest('granted_at');
    }

    public function activeBiometricConsent(): HasOne
    {
        return $this->hasOne(BiometricConsent::class, 'student_id')
            ->where('status', BiometricConsent::STATUS_GRANTED)
            ->latestOfMany('granted_at');
    }

    public function faceTemplate(): HasOne
    {
        return $this->hasOne(FaceTemplate::class, 'student_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'student_id', 'course_id')
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function teachingCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'professor_id');
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    /** Administración académica: superadmin, admin y coordinador. */
    public function isStaff(): bool
    {
        return in_array($this->role?->slug, self::STAFF_ROLES, true);
    }

    public function isCoordinator(): bool
    {
        return $this->role?->slug === 'coordinator';
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->whereRole('student');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'superadmin';
    }

    public function isTeacher(): bool
    {
        return $this->role?->slug === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role?->slug === 'student';
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function scopeWhereRole($query, string $slug)
    {
        return $query->whereHas('role', fn ($query) => $query->where('slug', $slug));
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
