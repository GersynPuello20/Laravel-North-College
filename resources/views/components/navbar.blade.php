
<div class="bg-white shadow-md px-8 py-4 flex items-center justify-between">

    <!-- LEFT -->
    <div>

        <h2 class="text-2xl font-bold text-blue-950">
            Dashboard {{ Auth::user()->role?->name ?? 'del sistema' }}
        </h2>

        <p class="text-gray-500 text-sm">
            Bienvenido al sistema North College
        </p>

    </div>

    <!-- RIGHT -->
    <div class="flex items-center gap-5">

        <!-- Notifications -->
        <button class="relative">

            <span class="text-2xl">
                🔔
            </span>

            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">

                3

            </span>

        </button>

        <!-- User -->
        <div class="flex items-center gap-3">

            <div class="text-right">

                <p class="font-semibold text-blue-950">
                    {{ Auth::user()->name }}
                </p>

                <p class="text-sm text-gray-500">
                    {{ Auth::user()->email }}
                </p>

                <p class="mt-1 inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                    {{ Auth::user()->role?->name ?? 'Sin asignar' }}
                </p>

            </div>

            <div class="w-12 h-12 rounded-full bg-blue-950 text-white flex items-center justify-center font-bold">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

        </div>

        <form method="POST" action="{{ route('logout') }}" class="flex items-center">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 transition-all duration-200">
                Cerrar sesión
            </button>
        </form>

    </div>

</div>

