@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin() || $user->isSuperAdmin();

    // [etiqueta, ruta, icono, patrón activo, parámetros]
    $groups = [];

    if ($user->isStaff() || $user->isTeacher()) {
        $groups[] = ['label' => null, 'items' => [
            ['Panel principal', 'dashboard', 'home', 'dashboard'],
        ]];
        $groups[] = ['label' => 'Asistencia', 'items' => [
            ['Sesiones de clase', 'sessions.index', 'calendar-days', ['sessions.index', 'sessions.create', 'sessions.show']],
            ['Reconocimiento facial', 'sessions.index', 'camera', 'sessions.facial', ['status' => 'open']],
            ['Reportes', 'reports.attendance', 'chart-bar', 'reports.*'],
        ]];
    }

    if ($user->isStaff()) {
        $groups[] = ['label' => 'Comunidad', 'items' => [
            ['Estudiantes', 'students.index', 'academic-cap', 'students.*'],
        ]];
    }

    if ($isAdmin) {
        $groups[] = ['label' => 'Gestión académica', 'items' => [
            ['Grupos', 'admin.courses.index', 'user-group', 'admin.courses.*'],
            ['Horarios', 'admin.schedules.index', 'clock', 'admin.schedules.*'],
            ['Matrículas', 'admin.enrollments.index', 'clipboard-document-list', 'admin.enrollments.*'],
            ['Asignaturas', 'admin.subjects.index', 'book-open', 'admin.subjects.*'],
            ['Notas', 'admin.grades.index', 'document-text', 'admin.grades.*'],
            ['Usuarios y roles', 'admin.users.index', 'users', 'admin.users.*'],
        ]];
    }

    if ($user->isTeacher()) {
        $groups[] = ['label' => 'Mis clases', 'items' => [
            ['Mis grupos', 'teacher.courses.index', 'user-group', 'teacher.courses.*'],
            ['Notas', 'teacher.grades.index', 'document-text', 'teacher.grades.*'],
        ]];
    }

    if ($user->isStudent()) {
        $groups[] = ['label' => null, 'items' => [
            ['Panel principal', 'dashboard', 'home', 'dashboard'],
            ['Mi asistencia', 'student.attendance', 'clipboard-document-check', 'student.attendance'],
            ['Mis datos biométricos', 'student.biometrics', 'shield-check', 'student.biometrics*'],
        ]];
        $groups[] = ['label' => 'Matrículas', 'items' => [
            ['Mis matrículas', 'student.enrollments.index', 'clipboard-document-list', 'student.enrollments.*'],
            ['Cursos disponibles', 'student.courses.available', 'book-open', 'student.courses.*'],
        ]];
    }

    if ($user->isStaff()) {
        $groups[] = ['label' => 'Sistema', 'items' => [
            ['Configuración', 'settings', 'cog-6-tooth', 'settings'],
        ]];
    }
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-navy-950 text-navy-100 transition-transform duration-200 lg:translate-x-0"
    :class="sidebarOpen && '!translate-x-0'"
    aria-label="Navegación principal"
>
    <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
        <span class="flex size-9 items-center justify-center rounded-lg bg-accent-500 text-sm font-bold text-white">NC</span>
        <div class="leading-tight">
            <p class="text-sm font-semibold tracking-wide text-white">North College</p>
            <p class="text-xs text-navy-300">Gestión académica</p>
        </div>
        <button type="button" class="ml-auto rounded-md p-1 text-navy-300 hover:text-white lg:hidden" @click="sidebarOpen = false" aria-label="Cerrar menú">
            <x-icon name="x-mark" />
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        @foreach ($groups as $group)
            <div>
                @if ($group['label'])
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-navy-400">{{ $group['label'] }}</p>
                @endif
                <ul class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php
                            [$label, $route, $icon, $pattern] = $item;
                            $params = $item[4] ?? [];
                            $active = request()->routeIs(...(array) $pattern)
                                && ! ($route === 'sessions.index' && empty($params) && request()->routeIs('sessions.facial'));
                        @endphp
                        <li>
                            <a href="{{ route($route, $params) }}"
                               @class([
                                   'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition',
                                   'bg-white/10 font-medium text-white' => $active,
                                   'text-navy-200 hover:bg-white/5 hover:text-white' => ! $active,
                               ])
                               @if ($active) aria-current="page" @endif>
                                <x-icon :name="$icon" @class(['size-5 shrink-0', 'text-accent-500' => $active, 'text-navy-400 group-hover:text-navy-200' => ! $active]) />
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-3">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 hover:bg-white/5">
            <span class="flex size-9 items-center justify-center rounded-full bg-navy-800 text-xs font-semibold text-white">{{ $user->initials() }}</span>
            <span class="min-w-0 leading-tight">
                <span class="block truncate text-sm font-medium text-white">{{ $user->name }}</span>
                <span class="block truncate text-xs text-navy-300">{{ $user->role?->name ?? 'Sin rol asignado' }}</span>
            </span>
        </a>
    </div>
</aside>
