<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\SessionAttendance;
use App\Models\Subject;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAcademicData;
use Tests\TestCase;

/** Cada pantalla se renderiza sin errores para el rol que la usa. */
class PagesRenderTest extends TestCase
{
    use BuildsAcademicData;
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        config(['biometrics.service_url' => 'http://127.0.0.1:1', 'biometrics.service_token' => 'x', 'biometrics.timeout' => 1]);

        $this->admin = User::factory()->admin()->create();
        $this->teacher = User::factory()->teacher()->create();
        $course = $this->course($this->teacher);
        $this->student = $this->enrolledStudent($course);
        $subject = Subject::create(['course_id' => $course->id, 'name' => 'Cálculo']);
        Schedule::create(['course_id' => $course->id, 'day_of_week' => 'lunes', 'start_time' => '08:00', 'end_time' => '10:00', 'classroom' => 'A-101']);
        Grade::create(['student_id' => $this->student->id, 'subject_id' => $subject->id, 'grade' => 85, 'created_by' => $this->teacher->id]);
        $session = $this->classSession($course);
        app(AttendanceService::class)->mark($session, $this->student, $this->teacher, status: SessionAttendance::STATUS_LATE);
    }

    public function test_admin_pages_render(): void
    {
        $session = ClassSession::first();
        $course = $session->course;
        $grade = Grade::first();

        $urls = [
            route('dashboard'), route('sessions.index'), route('sessions.create'), route('sessions.show', $session),
            route('sessions.facial', $session), route('reports.attendance'), route('reports.attendance', ['status' => 'late']), route('reports.attendance', ['method' => 'manual', 'student' => 'a', 'needs_review' => 1, 'corrected' => 1]),
            route('students.index'), route('students.show', $this->student), route('students.edit', $this->student),
            route('students.biometrics.show', $this->student), route('settings'),
            route('admin.users.index'), route('admin.courses.index'), route('admin.courses.create'), route('admin.courses.edit', $course),
            route('admin.subjects.index'), route('admin.subjects.create'), route('admin.schedules.index'), route('admin.schedules.create'),
            route('admin.enrollments.index'), route('admin.enrollments.index', ['course_id' => $course->id]), route('admin.grades.index'),
            route('profile.edit'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
        $this->assertNotNull($grade);
    }

    public function test_teacher_pages_render(): void
    {
        $session = ClassSession::first();

        foreach ([
            route('dashboard'), route('sessions.index'), route('sessions.show', $session), route('sessions.facial', $session),
            route('reports.attendance'), route('teacher.courses.index'), route('teacher.grades.index'),
            route('teacher.grades.create'), route('teacher.grades.edit', Grade::first()),
        ] as $url) {
            $this->actingAs($this->teacher)->get($url)->assertOk();
        }
    }

    public function test_student_pages_render(): void
    {
        foreach ([
            route('dashboard'), route('student.attendance'), route('student.biometrics'), route('student.courses.available'),
            route('student.enrollments.index'), route('student.enrollments.create'), route('profile.edit'),
        ] as $url) {
            $this->actingAs($this->student)->get($url)->assertOk();
        }
    }

    public function test_exports_download(): void
    {
        foreach (['csv', 'xlsx', 'pdf'] as $format) {
            $this->actingAs($this->admin)->get(route('reports.attendance.export', ['format' => $format]))
                ->assertOk()
                ->assertDownload();
        }
    }

    public function test_pending_user_sees_waiting_page(): void
    {
        $pending = User::factory()->role('pending')->create();
        $this->actingAs($pending)->get(route('dashboard'))->assertOk();
    }
}
