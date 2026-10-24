<?php

namespace Tests\Concerns;

use App\Models\BiometricConsent;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FaceTemplate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;

/** Datos mínimos para probar asistencia y biometría sin depender del seeder. */
trait BuildsAcademicData
{
    protected function course(?User $teacher = null): Course
    {
        return Course::create([
            'name' => 'Grupo '.fake()->unique()->numberBetween(1, 9999),
            'professor_id' => ($teacher ?? User::factory()->teacher()->create())->id,
            'status' => 'active',
        ]);
    }

    protected function enrolledStudent(Course $course, array $profile = []): User
    {
        $student = User::factory()->student()->create();
        Student::create(array_merge(['user_id' => $student->id, 'status' => 'active', 'birth_date' => now()->subYears(20)->toDateString()], $profile));
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        return $student;
    }

    protected function classSession(Course $course, string $status = ClassSession::STATUS_OPEN, ?Carbon $startsAt = null): ClassSession
    {
        $startsAt ??= now()->subMinutes(5)->startOfMinute();

        return ClassSession::create([
            'course_id' => $course->id,
            'session_date' => $startsAt->toDateString(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(),
            'late_after_minutes' => 10,
            'status' => $status,
        ]);
    }

    protected function consent(User $student, User $actor): BiometricConsent
    {
        return BiometricConsent::create([
            'student_id' => $student->id,
            'version' => config('biometrics.consent_version'),
            'status' => BiometricConsent::STATUS_GRANTED,
            'is_minor' => false,
            'granted_by_name' => $student->name,
            'granted_by_document' => '1000000',
            'granted_by_relationship' => 'self',
            'granted_at' => now(),
            'recorded_by' => $actor->id,
        ]);
    }

    /** Vector unitario de 128 dimensiones con un 1 en la posición indicada. */
    protected function vector(int $axis, float $mix = 0.0, int $other = 0): array
    {
        $v = array_fill(0, 128, 0.0);
        $v[$axis] = 1.0 - $mix;
        if ($mix > 0) {
            $v[$other] = $mix;
        }

        return $v;
    }

    protected function template(User $student, BiometricConsent $consent, array $embedding): FaceTemplate
    {
        return FaceTemplate::create([
            'student_id' => $student->id,
            'biometric_consent_id' => $consent->id,
            'embedding' => $embedding,
            'engine' => 'test-engine',
            'quality' => 0.95,
        ]);
    }

    protected function frames(int $count = 8): array
    {
        return array_fill(0, $count, 'data:image/jpeg;base64,'.base64_encode('fake-jpeg'));
    }
}
