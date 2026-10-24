<?php

namespace App\Http\Controllers\Facial;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaceCaptureRequest;
use App\Models\BiometricConsent;
use App\Models\FaceRecognitionAttempt;
use App\Models\User;
use App\Services\Biometrics\BiometricConsentService;
use App\Services\Biometrics\FaceEngineClient;
use App\Services\Biometrics\FaceEnrollmentService;
use App\Services\Biometrics\LivenessChallenge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

/** Autorización y enrolamiento facial de un estudiante (solo personal académico). */
class BiometricController extends Controller
{
    public function show(User $student): View
    {
        Gate::authorize('manage-biometrics');
        $this->ensureStudent($student);

        $student->load(['student', 'faceTemplate.enrolledBy', 'activeBiometricConsent.recordedBy', 'biometricConsents.recordedBy', 'biometricConsents.revokedBy']);

        $attempts = FaceRecognitionAttempt::where('student_id', $student->id)
            ->where('purpose', FaceRecognitionAttempt::PURPOSE_ENROLLMENT)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('biometrics.show', [
            'student' => $student,
            'profile' => $student->student,
            'consent' => $student->activeBiometricConsent,
            'template' => $student->faceTemplate,
            'attempts' => $attempts,
        ]);
    }

    public function storeConsent(Request $request, User $student, BiometricConsentService $consents): RedirectResponse
    {
        Gate::authorize('manage-biometrics');
        $this->ensureStudent($student);

        $data = $request->validate([
            'granted_by_name' => ['required', 'string', 'max:255'],
            'granted_by_document' => ['required', 'string', 'max:30'],
            'granted_by_relationship' => ['required', Rule::in(array_keys(BiometricConsent::RELATIONSHIPS))],
            'minor_was_heard' => ['nullable', 'boolean'],
            'informed' => ['accepted'],
            'voluntary' => ['accepted'],
        ], [
            'informed.accepted' => 'Confirma que se informó la finalidad y los derechos del titular.',
            'voluntary.accepted' => 'Confirma que la autorización es voluntaria y que existe la alternativa manual.',
        ]);

        try {
            $consents->grant($student, $data, $request->user());
        } catch (InvalidArgumentException $error) {
            return back()->withInput()->with('error', $error->getMessage());
        }

        return back()->with('success', 'Autorización registrada. Ya puedes enrolar el rostro.');
    }

    public function revokeConsent(Request $request, User $student, BiometricConsentService $consents): RedirectResponse
    {
        Gate::authorize('manage-biometrics');
        $this->ensureStudent($student);

        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $consent = $student->activeBiometricConsent;

        if (! $consent) {
            return back()->with('error', 'El estudiante no tiene una autorización vigente.');
        }

        $consents->revoke($consent, $request->user(), $data['reason']);

        return back()->with('success', 'Autorización revocada y datos biométricos eliminados.');
    }

    public function challenge(User $student, LivenessChallenge $challenges): JsonResponse
    {
        Gate::authorize('manage-biometrics');
        $this->ensureStudent($student);

        if (! $student->activeBiometricConsent) {
            return response()->json(['ok' => false, 'code' => 'no_consent', 'message' => 'Primero registra la autorización.'], 409);
        }

        return response()->json(['ok' => true, 'challenge' => $challenges->issue("enroll:{$student->id}")]);
    }

    public function detect(Request $request, User $student, FaceEngineClient $engine): JsonResponse
    {
        Gate::authorize('manage-biometrics');
        $this->ensureStudent($student);

        return AttendanceController::detectFrame($request, $engine);
    }

    public function enroll(FaceCaptureRequest $request, User $student, LivenessChallenge $challenges, FaceEnrollmentService $enrollment): JsonResponse
    {
        Gate::authorize('manage-biometrics');
        $this->ensureStudent($student);

        $direction = $challenges->consume($request->validated('challenge_id'), "enroll:{$student->id}");
        $result = $enrollment->enroll($student, $request->frames(), $direction, $request->user());

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    public function destroyTemplate(Request $request, User $student, FaceEnrollmentService $enrollment): RedirectResponse
    {
        Gate::authorize('manage-biometrics');
        $this->ensureStudent($student);

        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);

        return $enrollment->delete($student, $request->user(), $data['reason'])
            ? back()->with('success', 'Datos biométricos eliminados. La autorización sigue vigente.')
            : back()->with('error', 'El estudiante no tenía datos biométricos.');
    }

    /** Vista del propio estudiante: qué se guarda y cómo retirar la autorización. */
    public function mine(Request $request): View
    {
        $user = $request->user()->load(['activeBiometricConsent', 'faceTemplate', 'biometricConsents']);

        return view('biometrics.mine', [
            'consent' => $user->activeBiometricConsent,
            'template' => $user->faceTemplate,
            'history' => $user->biometricConsents,
            'isMinor' => $user->student?->isMinor(),
        ]);
    }

    public function revokeMine(Request $request, BiometricConsentService $consents): RedirectResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $consent = $request->user()->activeBiometricConsent;

        if (! $consent) {
            return back()->with('error', 'No tienes una autorización vigente.');
        }

        $consents->revoke($consent, $request->user(), ($data['reason'] ?? null) ?: 'Retirada por el titular.');

        return back()->with('success', 'Retiraste tu autorización. Tus datos biométricos fueron eliminados y tu asistencia se registrará de forma manual.');
    }

    private function ensureStudent(User $student): void
    {
        abort_unless($student->isStudent(), 404);
    }
}
