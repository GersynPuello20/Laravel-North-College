<?php

namespace Tests\Feature\Biometrics;

use App\Models\AuditLog;
use App\Models\BiometricConsent;
use App\Models\FaceTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\BuildsAcademicData;
use Tests\TestCase;

class EnrollmentAndConsentTest extends TestCase
{
    use BuildsAcademicData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'biometrics.service_url' => 'http://face.test',
            'biometrics.service_token' => 'test-token',
            'biometrics.liveness_required' => true,
        ]);
        Http::fake(['face.test/embed' => Http::response([
            'ok' => true, 'embedding' => $this->vector(3), 'engine' => 'test-engine', 'quality' => 0.9, 'liveness' => ['passed' => true],
        ])]);
    }

    private function enroll(User $actor, User $student)
    {
        $challenge = $this->actingAs($actor)->postJson(route('students.biometrics.challenge', $student));
        if ($challenge->status() !== 200) {
            return $challenge;
        }

        return $this->actingAs($actor)->postJson(route('students.biometrics.enroll', $student), [
            'challenge_id' => $challenge->json('challenge.id'),
            'frames' => $this->frames(),
        ]);
    }

    private function consentPayload(array $overrides = []): array
    {
        return array_merge([
            'granted_by_name' => 'Ana Pérez',
            'granted_by_document' => '1090000000',
            'granted_by_relationship' => 'self',
            'informed' => '1',
            'voluntary' => '1',
        ], $overrides);
    }

    public function test_enrollment_without_consent_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->enrolledStudent($this->course());

        $this->enroll($admin, $student)->assertStatus(409)->assertJson(['code' => 'no_consent']);

        $this->assertSame(0, FaceTemplate::count());
        Http::assertNothingSent();
    }

    public function test_enrollment_with_consent_stores_encrypted_template(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->enrolledStudent($this->course());
        $this->actingAs($admin)->post(route('students.biometrics.consent', $student), $this->consentPayload())->assertSessionHas('success');

        $this->enroll($admin, $student)->assertOk()->assertJson(['ok' => true, 'code' => 'enrolled']);

        $template = FaceTemplate::sole();
        $this->assertEquals($this->vector(3), $template->embedding);
        // En la base de datos solo está el texto cifrado, nunca el vector legible.
        $raw = DB::table('face_templates')->value('embedding');
        $this->assertStringNotContainsString('[0', $raw);
        $this->assertSame($this->vector(3), array_map('floatval', decrypt($raw, false) ? json_decode(decrypt($raw, false), true) : []));
        $this->assertArrayNotHasKey('embedding', $template->toArray());
        $this->assertTrue(AuditLog::where('action', 'biometric.enrolled')->exists());
    }

    public function test_consent_requires_explicit_declarations(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->enrolledStudent($this->course());

        $this->actingAs($admin)->post(route('students.biometrics.consent', $student), $this->consentPayload(['informed' => null, 'voluntary' => null]))
            ->assertSessionHasErrors(['informed', 'voluntary']);
        $this->assertSame(0, BiometricConsent::count());
    }

    public function test_minor_requires_legal_representative_and_being_heard(): void
    {
        $admin = User::factory()->admin()->create();
        $minor = $this->enrolledStudent($this->course(), ['birth_date' => now()->subYears(15)->toDateString()]);

        $this->actingAs($admin)->post(route('students.biometrics.consent', $minor), $this->consentPayload())->assertSessionHas('error');
        $this->actingAs($admin)->post(route('students.biometrics.consent', $minor), $this->consentPayload(['granted_by_relationship' => 'mother']))->assertSessionHas('error');
        $this->assertSame(0, BiometricConsent::count());

        $this->actingAs($admin)->post(route('students.biometrics.consent', $minor), $this->consentPayload(['granted_by_relationship' => 'mother', 'minor_was_heard' => '1']))
            ->assertSessionHas('success');
        $this->assertTrue(BiometricConsent::sole()->is_minor);
    }

    public function test_revoking_consent_deletes_template(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->enrolledStudent($this->course());
        $this->template($student, $this->consent($student, $admin), $this->vector(3));

        $this->actingAs($admin)->post(route('students.biometrics.revoke', $student), ['reason' => 'Solicitud del titular'])->assertSessionHas('success');

        $this->assertSame(0, FaceTemplate::count());
        $this->assertSame('revoked', BiometricConsent::sole()->status);
        $this->assertTrue(AuditLog::where('action', 'biometric.consent_revoked')->exists());
    }

    public function test_student_can_withdraw_own_consent(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->enrolledStudent($this->course());
        $this->template($student, $this->consent($student, $admin), $this->vector(3));

        $this->actingAs($student)->get(route('student.biometrics'))->assertOk();
        $this->actingAs($student)->post(route('student.biometrics.revoke'))->assertSessionHas('success');

        $this->assertSame(0, FaceTemplate::count());
    }

    public function test_same_face_cannot_be_enrolled_for_two_students(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();
        $first = $this->enrolledStudent($course);
        $this->template($first, $this->consent($first, $admin), $this->vector(3));
        $second = $this->enrolledStudent($course);
        $this->consent($second, $admin);

        $this->enroll($admin, $second)->assertStatus(422)->assertJson(['code' => 'duplicate_identity']);
        $this->assertSame(1, FaceTemplate::count());
    }

    public function test_biometric_routes_are_staff_only(): void
    {
        $course = $this->course();
        $student = $this->enrolledStudent($course);
        $other = $this->enrolledStudent($course);
        $teacher = $course->professor;

        foreach ([$teacher, $other] as $user) {
            $this->actingAs($user)->get(route('students.biometrics.show', $student))->assertForbidden();
            $this->actingAs($user)->postJson(route('students.biometrics.challenge', $student))->assertForbidden();
            $this->actingAs($user)->post(route('students.biometrics.consent', $student), $this->consentPayload())->assertForbidden();
        }
        $this->actingAs($teacher)->get(route('settings'))->assertForbidden();
        $this->assertSame(0, BiometricConsent::count());
    }

    public function test_staff_biometric_page_never_exposes_embedding(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->enrolledStudent($this->course());
        $template = $this->template($student, $this->consent($student, $admin), $this->vector(3));
        $cipher = DB::table('face_templates')->where('id', $template->id)->value('embedding');

        $this->actingAs($admin)->get(route('students.biometrics.show', $student))
            ->assertOk()
            ->assertDontSee($cipher)
            ->assertDontSee('embedding');
    }

    public function test_purge_command_removes_templates_without_active_consent(): void
    {
        $admin = User::factory()->admin()->create();
        $student = $this->enrolledStudent($this->course());
        $consent = $this->consent($student, $admin);
        $this->template($student, $consent, $this->vector(3));
        $kept = $this->enrolledStudent($this->course());
        $this->template($kept, $this->consent($kept, $admin), $this->vector(4));
        $consent->update(['status' => 'revoked']);

        $this->artisan('biometrics:purge')->assertSuccessful();

        $this->assertSame([$kept->id], FaceTemplate::pluck('student_id')->all());
    }
}
