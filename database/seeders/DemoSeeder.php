<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de demostración para probar el flujo en local. No corre con
 * `db:seed` normal: `php artisan db:seed --class=DemoSeeder`.
 * Todo queda marcado como "Demo" y no se crean datos biométricos.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $password = Hash::make('Password123!');
        $role = fn (string $slug) => Role::firstWhere('slug', $slug)->id;

        $teacher = User::updateOrCreate(['email' => 'tutor.demo@northcollege.test'], [
            'name' => 'Tutor Demo', 'password' => $password, 'role_id' => $role('teacher'), 'status' => 'active',
        ]);

        $course = Course::updateOrCreate(['name' => 'Grupo Demo 10A'], [
            'description' => 'Grupo de demostración', 'professor_id' => $teacher->id, 'status' => 'active',
        ]);

        Schedule::updateOrCreate(
            ['course_id' => $course->id, 'day_of_week' => Schedule::DAYS[now()->isoWeekday()]],
            ['start_time' => '07:00', 'end_time' => '09:00', 'classroom' => 'Aula Demo']
        );

        foreach ([['Estudiante Demo Uno', 20], ['Estudiante Demo Dos', 19], ['Estudiante Demo Tres (menor)', 15]] as $i => [$name, $age]) {
            $student = User::updateOrCreate(['email' => 'estudiante'.($i + 1).'.demo@northcollege.test'], [
                'name' => $name, 'password' => $password, 'role_id' => $role('student'), 'status' => 'active',
            ]);
            Student::updateOrCreate(['user_id' => $student->id], [
                'status' => 'active',
                'enrollment_number' => 'DEMO-'.($i + 1),
                'birth_date' => now()->subYears($age)->subDays(10)->toDateString(),
                'guardian_name' => $age < 18 ? 'Acudiente Demo' : null,
            ]);
            Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'course_id' => $course->id],
                ['enrollment_date' => now()->toDateString(), 'status' => 'active']
            );
        }

        app(AttendanceService::class)->createSessionsFromSchedules($course->load('schedules'), today(), $teacher);

        $this->command?->info('Demo lista: tutor.demo@northcollege.test / Password123! (y estudiante1..3.demo@northcollege.test).');
    }
}
