<?php

namespace Tests\Feature\Attendance;

use App\Models\SessionAttendance;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAcademicData;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use BuildsAcademicData;
    use RefreshDatabase;

    public function test_teacher_report_and_export_only_include_own_groups(): void
    {
        $teacher = User::factory()->teacher()->create();
        $mine = $this->course($teacher);
        $other = $this->course();
        $myStudent = $this->enrolledStudent($mine);
        $otherStudent = $this->enrolledStudent($other);
        $service = app(AttendanceService::class);
        $service->mark($this->classSession($mine), $myStudent, $teacher);
        $service->mark($this->classSession($other), $otherStudent, $other->professor, status: SessionAttendance::STATUS_LATE);

        $this->actingAs($teacher)->get(route('reports.attendance'))
            ->assertOk()
            ->assertSee($myStudent->name)
            ->assertDontSee($otherStudent->name);

        $csv = $this->actingAs($teacher)->get(route('reports.attendance.export', ['format' => 'csv']))->streamedContent();
        $this->assertStringContainsString($myStudent->name, $csv);
        $this->assertStringNotContainsString($otherStudent->name, $csv);
    }

    public function test_status_filter_counts_only_that_status(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();
        $session = $this->classSession($course);
        $late = $this->enrolledStudent($course);
        $present = $this->enrolledStudent($course);
        app(AttendanceService::class)->mark($session, $late, $admin, status: SessionAttendance::STATUS_LATE);
        app(AttendanceService::class)->mark($session, $present, $admin, status: SessionAttendance::STATUS_PRESENT);

        $this->actingAs($admin)->get(route('reports.attendance', ['status' => 'late']))
            ->assertOk()
            ->assertSee($late->name)
            ->assertDontSee($present->name);
    }
}
