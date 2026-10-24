<?php

namespace Tests\Feature\Biometrics;

use App\Models\ClassSession;
use App\Models\FaceRecognitionAttempt;
use App\Models\SessionAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\BuildsAcademicData;
use Tests\TestCase;

class FacialAttendanceTest extends TestCase
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
    }

    private function fakeEngine(array $embedding): void
    {
        Http::fake(['face.test/embed' => Http::response([
            'ok' => true,
            'embedding' => $embedding,
            'engine' => 'test-engine',
            'quality' => 0.9,
            'liveness' => ['passed' => true],
        ])]);
    }

    /** Pide el reto y envía la captura, como hace la pantalla de cámara. */
    private function recognize(User $actor, ClassSession $session)
    {
        $challenge = $this->actingAs($actor)->postJson(route('sessions.facial.challenge', $session))->assertOk()->json('challenge');

        return $this->actingAs($actor)->postJson(route('sessions.facial.recognize', $session), [
            'challenge_id' => $challenge['id'],
            'frames' => $this->frames(),
        ]);
    }

    /** @return array{0: User, 1: ClassSession, 2: User} */
    private function scenario(): array
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $student = $this->enrolledStudent($course);
        $this->template($student, $this->consent($student, $teacher), $this->vector(0));

        return [$teacher, $this->classSession($course), $student];
    }

    public function test_valid_match_registers_facial_attendance(): void
    {
        [$teacher, $session, $student] = $this->scenario();
        $this->fakeEngine($this->vector(0, 0.05, 1));

        $this->recognize($teacher, $session)->assertOk()->assertJson(['ok' => true, 'code' => 'matched']);

        $record = SessionAttendance::sole();
        $this->assertSame($student->id, $record->student_id);
        $this->assertSame(SessionAttendance::METHOD_FACIAL, $record->method);
        $this->assertNotNull($record->match_score);
        Http::assertSent(fn (Request $request) => $request->hasHeader('X-Service-Token', 'test-token') && $request['require_liveness'] === true && in_array($request['direction'], ['left', 'right'], true));
    }

    public function test_second_recognition_does_not_duplicate(): void
    {
        [$teacher, $session] = $this->scenario();
        $this->fakeEngine($this->vector(0));

        $this->recognize($teacher, $session)->assertJson(['code' => 'matched']);
        $this->recognize($teacher, $session)->assertJson(['code' => 'already_registered']);

        $this->assertSame(1, SessionAttendance::count());
    }

    public function test_unknown_face_registers_nothing(): void
    {
        [$teacher, $session] = $this->scenario();
        $this->fakeEngine($this->vector(5));

        $this->recognize($teacher, $session)->assertJson(['ok' => false, 'code' => 'unknown']);

        $this->assertSame(0, SessionAttendance::count());
        $this->assertSame('unknown', FaceRecognitionAttempt::sole()->result);
    }

    public function test_low_confidence_registers_nothing_and_hides_identity(): void
    {
        [$teacher, $session] = $this->scenario();
        // coseno = 0.3: por encima de "desconocido" y por debajo del umbral de coincidencia.
        $probe = $this->vector(1);
        $probe[0] = 0.3;
        $probe[1] = sqrt(1 - 0.09);
        $this->fakeEngine($probe);

        $response = $this->recognize($teacher, $session)->assertJson(['ok' => false, 'code' => 'low_confidence']);

        $this->assertArrayNotHasKey('student', $response->json());
        $this->assertSame(0, SessionAttendance::count());
        $this->assertNull(FaceRecognitionAttempt::sole()->student_id);
    }

    public function test_ambiguous_match_registers_nothing(): void
    {
        [$teacher, $session, $student] = $this->scenario();
        $twin = $this->enrolledStudent($session->course);
        $this->template($twin, $this->consent($twin, $teacher), $this->vector(0, 0.02, 1));
        $this->fakeEngine($this->vector(0, 0.01, 1));

        $this->recognize($teacher, $session)->assertJson(['ok' => false, 'code' => 'ambiguous']);
        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_only_students_of_the_group_are_candidates(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->course($teacher);
        $this->enrolledStudent($course);
        $outsider = $this->enrolledStudent($this->course());
        $this->template($outsider, $this->consent($outsider, $teacher), $this->vector(0));
        $this->fakeEngine($this->vector(0));

        $this->recognize($teacher, $this->classSession($course))->assertJson(['ok' => false, 'code' => 'no_candidates']);
        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_revoked_consent_excludes_template(): void
    {
        [$teacher, $session, $student] = $this->scenario();
        $student->activeBiometricConsent->update(['status' => 'revoked', 'revoked_at' => now()]);
        $this->fakeEngine($this->vector(0));

        $this->recognize($teacher, $session)->assertJson(['code' => 'no_candidates']);
        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_engine_down_falls_back_to_manual(): void
    {
        [$teacher, $session, $student] = $this->scenario();
        Http::fake(['face.test/*' => Http::response('boom', 500)]);

        $this->recognize($teacher, $session)->assertJson(['ok' => false, 'code' => 'engine_unavailable']);
        $this->assertSame(0, SessionAttendance::count());

        // La alternativa manual sigue disponible en la misma sesión.
        $this->actingAs($teacher)->post(route('sessions.attendances.store', $session), ['marks' => [$student->id => 'present']])
            ->assertSessionHas('success');
        $this->assertSame(SessionAttendance::METHOD_MANUAL, SessionAttendance::sole()->method);
    }

    public function test_failed_liveness_registers_nothing(): void
    {
        [$teacher, $session] = $this->scenario();
        Http::fake(['face.test/embed' => Http::response(['ok' => false, 'error' => 'liveness_failed', 'reason' => 'no_head_turn'])]);

        $this->recognize($teacher, $session)->assertJson(['ok' => false, 'code' => 'liveness_failed']);
        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_missing_or_reused_challenge_is_rejected(): void
    {
        [$teacher, $session] = $this->scenario();
        $this->fakeEngine($this->vector(0));

        $this->actingAs($teacher)->postJson(route('sessions.facial.recognize', $session), ['frames' => $this->frames()])
            ->assertJson(['code' => 'challenge_invalid']);

        $challenge = $this->actingAs($teacher)->postJson(route('sessions.facial.challenge', $session))->json('challenge');
        $payload = ['challenge_id' => $challenge['id'], 'frames' => $this->frames()];
        $this->actingAs($teacher)->postJson(route('sessions.facial.recognize', $session), $payload)->assertJson(['code' => 'matched']);
        SessionAttendance::query()->delete();
        $this->actingAs($teacher)->postJson(route('sessions.facial.recognize', $session), $payload)->assertJson(['code' => 'challenge_invalid']);

        $this->assertSame(0, SessionAttendance::count());
        Http::assertSentCount(1);
    }

    public function test_closed_session_rejects_facial_attendance(): void
    {
        [$teacher, $session] = $this->scenario();
        $session->update(['status' => ClassSession::STATUS_CLOSED]);

        $this->actingAs($teacher)->postJson(route('sessions.facial.challenge', $session))->assertStatus(409);
        $this->assertSame(0, SessionAttendance::count());
    }

    public function test_other_teacher_cannot_use_facial_attendance(): void
    {
        [, $session] = $this->scenario();

        $this->actingAs(User::factory()->teacher()->create())
            ->postJson(route('sessions.facial.challenge', $session))
            ->assertForbidden();
    }

    public function test_non_jpeg_frames_are_rejected(): void
    {
        [$teacher, $session] = $this->scenario();
        Http::fake();

        $this->actingAs($teacher)->postJson(route('sessions.facial.recognize', $session), ['frames' => ['data:image/png;base64,AAAA']])
            ->assertUnprocessable();
        Http::assertNothingSent();
    }
}
