<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\ClassSession;
use App\Models\SessionAttendance;
use App\Models\User;
use App\Services\Attendance\AttendanceException;
use App\Services\Attendance\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAcademicData;
use Tests\TestCase;

class ManualAttendanceTest extends TestCase
{
    use BuildsAcademicData;
    use RefreshDatabase;

    public function test_teacher_marks_manual_attendance(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $student = $this->enrolledStudent($course);
        $session = $this->classSession($course);

        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$student->id => 'excused']])
            ->assertSessionHas('success');

        $record = SessionAttendance::sole();
        $this->assertSame(SessionAttendance::STATUS_EXCUSED, $record->status);
        $this->assertSame(SessionAttendance::METHOD_MANUAL, $record->method);
        $this->assertSame($teacher->id, $record->recorded_by);
    }

    public function test_auto_status_uses_late_threshold(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $onTime = $this->enrolledStudent($course);
        $late = $this->enrolledStudent($course);
        $onTimeSession = $this->classSession($course, ClassSession::STATUS_OPEN, now()->subMinutes(5)->startOfMinute());
        $lateSession = $this->classSession($course, ClassSession::STATUS_OPEN, now()->subMinutes(30)->startOfMinute());

        $this->actingAs($teacher)->post(route('sessions.attendances.store', $onTimeSession), ['marks' => [$onTime->id => 'auto']]);
        $this->actingAs($teacher)->post(route('sessions.attendances.store', $lateSession), ['marks' => [$late->id => 'auto']]);

        $this->assertSame(SessionAttendance::STATUS_PRESENT, $onTimeSession->attendances()->value('status'));
        $this->assertSame(SessionAttendance::STATUS_LATE, $lateSession->attendances()->value('status'));
    }

    public function test_duplicate_attendance_is_prevented(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $student = $this->enrolledStudent($course);
        $session = $this->classSession($course);

        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$student->id => 'present']]);
        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$student->id => 'late']])
            ->assertSessionHas('error');

        $this->assertSame(1, SessionAttendance::count());
        $this->assertSame(SessionAttendance::STATUS_PRESENT, SessionAttendance::value('status'));
    }

    public function test_service_rejects_duplicates_even_without_controller(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $student = $this->enrolledStudent($course);
        $session = $this->classSession($course);
        $service = app(AttendanceService::class);

        $service->mark($session, $student, $teacher);

        $this->expectException(AttendanceException::class);
        $service->mark($session, $student, $teacher, SessionAttendance::METHOD_FACIAL);
    }

    public function test_student_outside_the_group_cannot_be_marked(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $outsider = $this->enrolledStudent($this->course());
        $session = $this->classSession($course);

        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$outsider->id => 'present']])
            ->assertSessionHas('error');

        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_teacher_cannot_mark_in_another_teachers_session(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course();
        $student = $this->enrolledStudent($course);
        $session = $this->classSession($course);

        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$student->id => 'present']])
            ->assertForbidden();
    }

    public function test_correction_requires_reason_and_is_traceable(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $student = $this->enrolledStudent($course);
        $session = $this->classSession($course);
        $record = app(AttendanceService::class)->mark($session, $student, $teacher, status: SessionAttendance::STATUS_ABSENT);

        $this->actingAs($teacher)->put(route('attendances.update', $record), ['status' => 'excused'])
            ->assertSessionHasErrors('reason');
        $this->assertSame(SessionAttendance::STATUS_ABSENT, $record->fresh()->status);

        $this->actingAs($teacher)->put(route('attendances.update', $record), ['status' => 'excused', 'reason' => 'Presentó incapacidad médica'])
            ->assertSessionHas('success');

        $correction = AttendanceCorrection::sole();
        $this->assertSame(SessionAttendance::STATUS_EXCUSED, $record->fresh()->status);
        $this->assertSame('absent', $correction->old_status);
        $this->assertSame('excused', $correction->new_status);
        $this->assertSame($teacher->id, $correction->changed_by);
        $this->assertSame('Presentó incapacidad médica', $correction->reason);
        $this->assertTrue(AuditLog::where('action', 'like', 'attendance.%')->exists());
    }

    public function test_other_teacher_cannot_correct_records(): void
    {
        $owner = User::factory()->teacher()->create();
        $course = $this->course($owner);
        $student = $this->enrolledStudent($course);
        $record = app(AttendanceService::class)->mark($this->classSession($course), $student, $owner);

        $this->actingAs(User::factory()->teacher()->create())
            ->put(route('attendances.update', $record), ['status' => 'absent', 'reason' => 'Intento no autorizado'])
            ->assertForbidden();

        $this->assertSame(0, AttendanceCorrection::count());
    }

    public function test_student_sees_only_own_attendance(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $me = $this->enrolledStudent($course);
        $other = $this->enrolledStudent($course);
        $session = $this->classSession($course);
        app(AttendanceService::class)->mark($session, $me, $teacher);
        app(AttendanceService::class)->mark($session, $other, $teacher, status: SessionAttendance::STATUS_LATE);

        $this->actingAs($me)->get(route('student.attendance'))
            ->assertOk()
            ->assertSee('Presente')
            ->assertDontSee($other->name);
    }
}
