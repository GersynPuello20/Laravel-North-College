<?php

namespace App\Services\Attendance;

use RuntimeException;

class AttendanceException extends RuntimeException
{
    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }

    public static function sessionNotOpen(): self
    {
        return new self('session_closed', 'La sesión no está abierta. Ábrela para registrar asistencia.');
    }

    public static function notEnrolled(): self
    {
        return new self('not_enrolled', 'El estudiante no tiene matrícula activa en este grupo.');
    }

    public static function alreadyRegistered(): self
    {
        return new self('already_registered', 'El estudiante ya tiene asistencia registrada en esta sesión.');
    }

    public static function invalidTransition(string $message): self
    {
        return new self('invalid_transition', $message);
    }
}
