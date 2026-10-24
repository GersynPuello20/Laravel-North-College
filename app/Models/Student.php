<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Perfil académico de un usuario con rol estudiante. Los datos de acceso
 * (nombre, correo) viven en `users`; aquí van el código, el documento, la
 * fecha de nacimiento y el acudiente, necesarios para tratar a menores.
 */
class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'enrollment_number',
        'document_number',
        'birth_date',
        'guardian_name',
        'guardian_document',
        'guardian_email',
        'guardian_phone',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function age(): ?int
    {
        return $this->birth_date?->age;
    }

    /** null cuando no hay fecha de nacimiento: no se puede saber. */
    public function isMinor(): ?bool
    {
        $age = $this->age();

        return $age === null ? null : $age < config('biometrics.adult_age', 18);
    }
}
