
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
           class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-900 hover:bg-red-600 transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-blue-900' : '' }}">

            <span>📊</span>

            <span class="font-medium">
                Dashboard
            </span>

        </a>

        <a href="{{ route('users.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('users.*') ? 'bg-blue-900' : '' }}">

            <span>👥</span>

            <span>
                Usuarios
            </span>

        </a>

        <a href="{{ route('students.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('students.*') ? 'bg-blue-900' : '' }}">

            <span>🎓</span>

            <span>
                Estudiantes
            </span>

        </a>

        <a href="{{ route('tutors.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('tutors.*') ? 'bg-blue-900' : '' }}">

            <span>👨‍🏫</span>

            <span>
                Docentes
            </span>

        </a>

        <a href="{{ route('attendances.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300 {{ request()->routeIs('attendances.*') ? 'bg-blue-900' : '' }}">

            <span>📅</span>

            <span>
                Asistencia
            </span>

        </a>

        <a href="#"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300">

            <span>📷</span>

            <span>
                Reconocimiento Facial
            </span>

        </a>

        <a href="#"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-900 transition-all duration-300">

            <span>⚙️</span>

            <span>
                Configuración
            </span>

        </a>

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
