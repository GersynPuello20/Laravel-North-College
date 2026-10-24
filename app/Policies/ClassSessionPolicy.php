<?php

namespace App\Policies;

use App\Models\ClassSession;
use App\Models\Course;
use App\Models\User;

/**
 * Personal académico: todos los grupos. Tutor (docente): solo las sesiones
 * de los grupos que tiene asignados.
 */
class ClassSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isTeacher();
    }

    public function view(User $user, ClassSession $session): bool
    {
        return $this->ownsCourse($user, $session->course);
    }

    public function create(User $user, ?Course $course = null): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isTeacher() && ($course === null || $course->professor_id === $user->id);
    }

    /** Abrir, cerrar, registrar y tomar asistencia facial. */
    public function manage(User $user, ClassSession $session): bool
    {
        return $this->ownsCourse($user, $session->course);
    }

    public function delete(User $user, ClassSession $session): bool
    {
        return $user->isStaff() && ! $session->isClosed() && ! $session->attendances()->exists();
    }

    private function ownsCourse(User $user, ?Course $course): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isTeacher() && $course !== null && $course->professor_id === $user->id;
    }
}
