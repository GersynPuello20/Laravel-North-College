<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\SessionAttendance;
use App\Models\User;
use App\Services\Attendance\AttendanceException;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassSessionController extends Controller
{
    public function __construct(private AttendanceService $attendance) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ClassSession::class);

        /** @var User $user */
        $user = $request->user();
        $filters = $request->validate([
            'course_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(array_keys(ClassSession::STATUS_LABELS))],
            'date' => ['nullable', 'date'],
        ]);

        $sessions = $this->visibleSessions($user)
            ->with(['course.professor'])
            ->withCount([
                'attendances',
                'attendances as present_count' => fn ($q) => $q->whereIn('status', [SessionAttendance::STATUS_PRESENT, SessionAttendance::STATUS_LATE]),
            ])
            ->when($filters['course_id'] ?? null, fn ($q, $id) => $q->where('course_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('session_date', $date))
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('sessions.index', [
            'sessions' => $sessions,
            'courses' => $this->visibleCourses($user),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', ClassSession::class);

        return view('sessions.create', [
            'courses' => $this->visibleCourses($request->user()),
            'selectedCourse' => $request->integer('course_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'late_after_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'topic' => ['nullable', 'string', 'max:255'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        Gate::authorize('create', [ClassSession::class, $course]);

        $date = Carbon::parse($data['session_date'])->startOfDay();
        $startsAt = $date->copy()->setTimeFromTimeString($data['start_time']);

        if (ClassSession::where('course_id', $course->id)->where('starts_at', $startsAt)->exists()) {
            return back()->withInput()->withErrors(['start_time' => 'Ya existe una sesión de este grupo a esa hora.']);
        }

        $session = ClassSession::create([
            'course_id' => $course->id,
            'session_date' => $date->toDateString(),
            'starts_at' => $startsAt,
            'ends_at' => $date->copy()->setTimeFromTimeString($data['end_time']),
            'late_after_minutes' => $data['late_after_minutes'],
            'topic' => $data['topic'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('sessions.show', $session)->with('success', 'Sesión creada. Ábrela cuando empiece la clase.');
    }

    /** Crea las sesiones de una fecha a partir de los horarios de los grupos visibles. */
    public function generate(Request $request): RedirectResponse
    {
        Gate::authorize('create', ClassSession::class);

        $data = $request->validate(['date' => ['required', 'date']]);
        $date = Carbon::parse($data['date'])->startOfDay();

        $created = $this->visibleCourses($request->user())
            ->load('schedules')
            ->flatMap(fn (Course $course) => $this->attendance->createSessionsFromSchedules($course, $date, $request->user()))
            ->filter(fn (ClassSession $session) => $session->wasRecentlyCreated)
            ->count();

        $message = $created > 0
            ? "Se crearon {$created} sesiones a partir de los horarios."
            : 'No había sesiones nuevas que crear para esa fecha según los horarios.';

        return redirect()->route('sessions.index', ['date' => $date->toDateString()])->with('success', $message);
    }

    public function show(ClassSession $classSession): View
    {
        Gate::authorize('view', $classSession);

        $classSession->load(['course.professor', 'openedBy', 'closedBy']);
        $records = $classSession->attendances()->with(['recordedBy', 'corrections.changedBy'])->get()->keyBy('student_id');
        $roster = $this->attendance->roster($classSession)->load('faceTemplate');

        // Registros de estudiantes que ya no están en la lista (matrícula retirada).
        $orphans = $records->except($roster->pluck('id')->all())->load('student');

        $summary = collect(SessionAttendance::STATUS_LABELS)->map(fn ($label, $status) => $records->where('status', $status)->count());

        return view('sessions.show', [
            'session' => $classSession,
            'roster' => $roster,
            'records' => $records,
            'orphans' => $orphans,
            'summary' => $summary,
            'pending' => $roster->count() - $roster->filter(fn ($student) => $records->has($student->id))->count(),
        ]);
    }

    public function open(Request $request, ClassSession $classSession): RedirectResponse
    {
        Gate::authorize('manage', $classSession);

        return $this->transition(fn () => $this->attendance->open($classSession, $request->user()), 'Sesión abierta. Ya puedes registrar asistencia.');
    }

    public function close(Request $request, ClassSession $classSession): RedirectResponse
    {
        Gate::authorize('manage', $classSession);

        try {
            $absent = $this->attendance->close($classSession, $request->user());
        } catch (AttendanceException $error) {
            return back()->with('error', $error->getMessage());
        }

        return back()->with('success', "Sesión cerrada. {$absent} estudiantes sin registro quedaron como ausentes.");
    }

    public function destroy(ClassSession $classSession): RedirectResponse
    {
        Gate::authorize('delete', $classSession);

        $classSession->delete();

        return redirect()->route('sessions.index')->with('success', 'Sesión eliminada.');
    }

    private function transition(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (AttendanceException $error) {
            return back()->with('error', $error->getMessage());
        }

        return back()->with('success', $message);
    }

    private function visibleSessions(User $user)
    {
        return $user->isStaff() ? ClassSession::query() : ClassSession::forTeacher($user->id);
    }

    private function visibleCourses(User $user)
    {
        return Course::query()
            ->when(! $user->isStaff(), fn ($q) => $q->where('professor_id', $user->id))
            ->orderBy('name')
            ->get();
    }
}
