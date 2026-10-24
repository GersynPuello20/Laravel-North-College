<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @deprecated Módulo anterior (entradas/salidas sin sesión). Lo reemplazan ClassSession y
 * SessionAttendance (asistencia por estudiante). Se conserva para no perder
 * los datos existentes en la tabla; ya no tiene rutas ni vistas.
 */
class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_type',
        'recorded_at',
        'note',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
