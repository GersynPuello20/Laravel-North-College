<?php

namespace Tests\Feature\Attendance;

use App\Models\AuditLog;
use App\Models\ClassSession;
use App\Models\SessionAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAcademicData;
use Tests\TestCase;

class ClassSessionTest extends TestCase
{
    use BuildsAcademicData;
    use RefreshDatabase;

    public function test_teacher_creates_a_session_for_own_group(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);

        $response = $this->actingAs($teacher)->post(route('sessions.store'), [
            'course_id' => $course->id,
            'session_date' => '2026-10-26',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'late_after_minutes' => 15,
            'topic' => 'Ecuaciones lineales',
        ]);

        $session = ClassSession::sole();
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertSame(ClassSession::STATUS_SCHEDULED, $session->status);
        $this->assertSame('2026-10-26 08:00', $session->starts_at->format('Y-m-d H:i'));
    }

    public function test_duplicate_session_at_same_time_is_rejected(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $payload = ['course_id' => $course->id, 'session_date' => '2026-10-26', 'start_time' => '08:00', 'end_time' => '10:00', 'late_after_minutes' => 10];

        $this->actingAs($teacher)->post(route('sessions.store'), $payload);
        $this->actingAs($teacher)->post(route('sessions.store'), $payload)->assertSessionHasErrors('start_time');

        $this->assertSame(1, ClassSession::count());
    }

    public function test_teacher_cannot_create_or_see_sessions_of_another_group(): void
    {
        $teacher = User::factory()->teacher()->create();
        $foreign = $this->course();
        $session = $this->classSession($foreign);

        $this->actingAs($teacher)->post(route('sessions.store'), [
            'course_id' => $foreign->id, 'session_date' => '2026-10-26', 'start_time' => '08:00', 'end_time' => '09:00', 'late_after_minutes' => 10,
        ])->assertForbidden();

        $this->actingAs($teacher)->get(route('sessions.show', $session))->assertForbidden();
        $this->actingAs($teacher)->get(route('sessions.index'))->assertOk()->assertDontSee($foreign->name);
    }

    public function test_listing_shows_own_sessions(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $this->classSession($course, ClassSession::STATUS_SCHEDULED, now()->startOfHour());

        $this->actingAs($teacher)->get(route('sessions.index', ['date' => now()->toDateString()]))
            ->assertOk()
            ->assertSee($course->name);
    }

    public function test_students_cannot_reach_attendance_module(): void
    {
        $course = $this->course();
        $student = $this->enrolledStudent($course);
        $session = $this->classSession($course);

        $this->actingAs($student)->get(route('sessions.index'))->assertForbidden();
        $this->actingAs($student)->get(route('sessions.show', $session))->assertForbidden();
        $this->actingAs($student)->post(route('sessions.attendances.store', $session), ['marks' => [$student->id => 'present']])->assertForbidden();
        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_open_and_close_flow_marks_absences_only_on_close(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $present = $this->enrolledStudent($course);
        $missing = $this->enrolledStudent($course);
        $session = $this->classSession($course, ClassSession::STATUS_SCHEDULED);

        $this->actingAs($teacher)->post(route('sessions.open', $session))->assertRedirect();
        $this->assertTrue($session->fresh()->isOpen());

        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$present->id => 'present']]);
        $this->assertSame(0, SessionAttendance::where('status', SessionAttendance::STATUS_ABSENT)->count());

        $this->actingAs($teacher)->post(route('sessions.close', $session))->assertRedirect();

        $session->refresh();
        $this->assertTrue($session->isClosed());
        $absent = SessionAttendance::where('student_id', $missing->id)->sole();
        $this->assertSame(SessionAttendance::STATUS_ABSENT, $absent->status);
        $this->assertSame(SessionAttendance::METHOD_SESSION_CLOSE, $absent->method);
        $this->assertSame(SessionAttendance::STATUS_PRESENT, SessionAttendance::where('student_id', $present->id)->value('status'));
        $this->assertTrue(AuditLog::where('action', 'session.closed')->exists());
    }

    public function test_closed_session_rejects_new_marks(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $student = $this->enrolledStudent($course);
        $session = $this->classSession($course, ClassSession::STATUS_CLOSED);

        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$student->id => 'present']])
            ->assertSessionHas('error');

        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_scheduled_session_rejects_marks_until_opened(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $student = $this->enrolledStudent($course);
        $session = $this->classSession($course, ClassSession::STATUS_SCHEDULED);

        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$student->id => 'present']])
            ->assertSessionHas('error');
        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_session_cannot_be_closed_twice(): void
    {
        $teacher = User::factory()->teacher()->create();
        $session = $this->classSession($this->course($teacher), ClassSession::STATUS_CLOSED);

        $this->actingAs($teacher)->post(route('sessions.close', $session))->assertSessionHas('error');
    }
}
