<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Schedule extends Model
{
    use HasFactory;

    /** Día ISO (1 = lunes) => nombre guardado en `day_of_week`. */
    public const DAYS = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo',
    ];

    public const DAY_LABELS = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    protected $fillable = [
        'course_id',
        'day_of_week',
        'start_time',
        'end_time',
        'classroom',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    /** Acepta textos antiguos escritos a mano ("Miércoles", "MARTES"...). */
    public static function normalizeDay(?string $day): ?string
    {
        $key = Str::of((string) $day)->ascii()->lower()->trim()->value();

        return array_key_exists($key, self::DAY_LABELS) ? $key : null;
    }

    public function isoDay(): ?int
    {
        $day = self::normalizeDay($this->day_of_week);

        return $day ? array_search($day, self::DAYS, true) : null;
    }

    public function dayLabel(): string
    {
        $day = self::normalizeDay($this->day_of_week);

        return $day ? self::DAY_LABELS[$day] : (string) $this->day_of_week;
    }
}
