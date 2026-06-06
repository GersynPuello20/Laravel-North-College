
<div class="w-72 bg-blue-950 text-white flex flex-col shadow-2xl">

    <!-- LOGO -->
    <div class="p-6 border-b border-blue-800">

        <h1 class="text-3xl font-extrabold tracking-wide">
            NORTH
        </h1>

        <p class="text-red-400 text-sm mt-1">
            College System
        </p>

    </div>

    <!-- MENU -->
    <nav class="flex-1 px-4 py-6 space-y-3">

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-blue-900' : '' }}">

            <span>📊</span>

            <span class="font-medium">
                Dashboard
            </span>

        </a>

        @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('admin.users.*') ? 'bg-blue-900' : '' }}">

                <span>👥</span>

                <span>
                    Usuarios
                </span>

            </a>

            <a href="{{ route('admin.courses.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('admin.courses.*') ? 'bg-blue-900' : '' }}">

                <span>📚</span>

                <span>
                    Cursos
                </span>

            </a>

            <a href="{{ route('admin.subjects.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('admin.subjects.*') ? 'bg-blue-900' : '' }}">

                <span>📝</span>

                <span>
                    Asignaturas
                </span>

            </a>

            <a href="{{ route('admin.schedules.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('admin.schedules.*') ? 'bg-blue-900' : '' }}">

                <span>⏰</span>

                <span>
                    Horarios
                </span>

            </a>

            <a href="{{ route('admin.enrollments.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('admin.enrollments.*') ? 'bg-blue-900' : '' }}">

                <span>🧾</span>

                <span>
                    Matrículas
                </span>

            </a>

            <a href="{{ route('admin.attendances.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('admin.attendances.*') ? 'bg-blue-900' : '' }}">

                <span>✅</span>

                <span>
                    Asistencias
                </span>

            </a>

            <a href="{{ route('admin.grades.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('admin.grades.*') ? 'bg-blue-900' : '' }}">

                <span>📈</span>

                <span>
                    Notas
                </span>

            </a>
        @elseif(Auth::user()->isTeacher())
            <a href="{{ route('teacher.courses.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('teacher.courses.*') ? 'bg-blue-900' : '' }}">

                <span>📚</span>

                <span>
                    Mis cursos
                </span>

            </a>

            <a href="{{ route('teacher.grades.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('teacher.grades.*') ? 'bg-blue-900' : '' }}">

                <span>📈</span>

                <span>
                    Mis notas
                </span>

            </a>

            <a href="{{ route('teacher.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('teacher.dashboard') ? 'bg-blue-900' : '' }}">

                <span>🗓️</span>

                <span>
                    Horarios
                </span>

            </a>
        @elseif(Auth::user()->isStudent())
            <a href="{{ route('student.courses.available') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('student.courses.*') ? 'bg-blue-900' : '' }}">

                <span>📚</span>

                <span>
                    Cursos disponibles
                </span>

            </a>

            <a href="{{ route('student.enrollments.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('student.enrollments.*') ? 'bg-blue-900' : '' }}">

                <span>🧾</span>

                <span>
                    Mis matrículas
                </span>

            </a>

            <a href="{{ route('student.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('student.dashboard') ? 'bg-blue-900' : '' }}">

                <span>📈</span>

                <span>
                    Mis notas
                </span>

            </a>
        @endif

    </nav>

    <!-- USER -->
    <div class="p-4 border-t border-blue-800">

        <div class="bg-blue-900 rounded-xl p-4 flex items-center gap-3">

            <div class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center font-bold text-white">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

            <div>

                <p class="font-semibold">
                    {{ Auth::user()->name }}
                </p>

                <p class="text-sm text-gray-300">
                    {{ Auth::user()->role?->name ?? 'Sin asignar' }}
                </p>

            </div>

        </div>

    </div>

</div>
