<?php

namespace App\Http\Controllers\Facial;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaceCaptureRequest;
use App\Models\ClassSession;
use App\Models\FaceTemplate;
use App\Services\Attendance\AttendanceService;
use App\Services\Biometrics\CaptureMessages;
use App\Services\Biometrics\FaceEngineClient;
use App\Services\Biometrics\FaceEngineUnavailable;
use App\Services\Biometrics\FacialAttendanceService;
use App\Services\Biometrics\LivenessChallenge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Toma de asistencia por reconocimiento facial dentro de una sesión. */
class AttendanceController extends Controller
{
    public function show(ClassSession $classSession, AttendanceService $attendance): View
    {
        Gate::authorize('manage', $classSession);

        $classSession->load(['course.professor', 'schedule']);
        $roster = $attendance->roster($classSession);
        $enrolled = FaceTemplate::whereIn('student_id', $roster->pluck('id'))
            ->whereHas('consent', fn ($q) => $q->active())
            ->count();

        $recent = $classSession->attendances()
            ->with('student')
            ->latest('recorded_at')
            ->limit(8)
            ->get();

        return view('facial.attendance', [
            'session' => $classSession,
            'rosterCount' => $roster->count(),
            'enrolledCount' => $enrolled,
            'registeredCount' => $classSession->attendances()->count(),
            'recent' => $recent,
        ]);
    }

    public function challenge(ClassSession $classSession, LivenessChallenge $challenges): JsonResponse
    {
        Gate::authorize('manage', $classSession);

        if (! $classSession->isOpen()) {
            return response()->json(['ok' => false, 'code' => 'session_closed', 'message' => 'La sesión no está abierta.'], 409);
        }

        return response()->json(['ok' => true, 'challenge' => $challenges->issue("session:{$classSession->id}")]);
    }

    /** Comprobación rápida de un fotograma para guiar a la persona (sin identificar). */
    public function detect(Request $request, ClassSession $classSession, FaceEngineClient $engine): JsonResponse
    {
        Gate::authorize('manage', $classSession);

        return self::detectFrame($request, $engine);
    }

    public function recognize(FaceCaptureRequest $request, ClassSession $classSession, LivenessChallenge $challenges, FacialAttendanceService $service): JsonResponse
    {
        Gate::authorize('manage', $classSession);

        $direction = $challenges->consume($request->validated('challenge_id'), "session:{$classSession->id}");

        return response()->json($service->recognize($classSession, $request->frames(), $direction, $request->user()));
    }

    public static function detectFrame(Request $request, FaceEngineClient $engine): JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'string', 'max:2000000', 'starts_with:data:image/jpeg;base64,'],
        ]);

        try {
            $result = $engine->detect($data['image']);
        } catch (FaceEngineUnavailable $error) {
            return response()->json(['ok' => false, 'code' => 'engine_unavailable', 'message' => $error->getMessage()], 503);
        }

        $issues = $result['frame']['issues'] ?? [];
        $message = $issues ? CaptureMessages::issue($issues[0]) : 'Rostro detectado.';

        return response()->json([
            'ok' => (bool) ($result['ok'] ?? false),
            'code' => $issues[0] ?? 'face_ok',
            'message' => $message ?? 'Ajusta tu posición.',
            'yaw' => $result['frame']['yaw'] ?? null,
        ]);
    }
}
