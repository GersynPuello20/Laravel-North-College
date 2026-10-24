<?php

namespace App\Policies;

use App\Models\SessionAttendance;
use App\Models\User;

class SessionAttendancePolicy
{
    /** Corregir un registro: personal académico o el tutor del grupo. */
    public function update(User $user, SessionAttendance $attendance): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isTeacher() && $attendance->classSession->course?->professor_id === $user->id;
    }
}
