
@extends('layouts.app')

@section('content')

<!-- TITLE -->
<div class="mb-8">

    <h1 class="text-4xl font-bold text-blue-950">
        Panel Principal
    </h1>

    <p class="text-gray-500 mt-2">
        Resumen general del sistema académico
    </p>

</div>

<!-- CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    <!-- CARD -->
    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-8 border-blue-900">

        <div class="flex justify-between items-center">

            <div>

                <p class="text-gray-500">
                    Estudiantes
                </p>

                <h2 class="text-4xl font-bold mt-2 text-blue-950">
                    1,250
                </h2>

            </div>

            <div class="text-5xl">
                🎓
            </div>

        </div>

    </div>

    <!-- CARD -->
    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-8 border-red-600">

        <div class="flex justify-between items-center">

            <div>

                <p class="text-gray-500">
                    Docentes
                </p>

                <h2 class="text-4xl font-bold mt-2 text-blue-950">
                    85
                </h2>

            </div>

            <div class="text-5xl">
                👨‍🏫
            </div>

        </div>

    </div>

    <!-- CARD -->
    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-8 border-green-500">

        <div class="flex justify-between items-center">

            <div>

                <p class="text-gray-500">
                    Asistencias Hoy
                </p>

                <h2 class="text-4xl font-bold mt-2 text-blue-950">
                    92%
                </h2>

            </div>

            <div class="text-5xl">
                📅
            </div>

        </div>

    </div>

    <!-- CARD -->
    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-8 border-yellow-500">

        <div class="flex justify-between items-center">

            <div>

                <p class="text-gray-500">
                    Reconocimientos
                </p>

                <h2 class="text-4xl font-bold mt-2 text-blue-950">
                    540
                </h2>

            </div>

            <div class="text-5xl">
                📷
            </div>

        </div>

    </div>

</div>

<!-- SECOND SECTION -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-8">

    <!-- CHART -->
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-lg p-6">

        <div class="flex justify-between items-center mb-6">

            <h2 class="text-2xl font-bold text-blue-950">
                Estadísticas
            </h2>

            <button class="bg-blue-950 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-all">

                Ver Reporte

            </button>

        </div>

        <!-- Fake chart -->
        <div class="h-80 flex items-end gap-4">

            <div class="bg-blue-900 w-full rounded-t-xl h-40"></div>
            <div class="bg-red-500 w-full rounded-t-xl h-56"></div>
            <div class="bg-blue-900 w-full rounded-t-xl h-32"></div>
            <div class="bg-red-500 w-full rounded-t-xl h-72"></div>
            <div class="bg-blue-900 w-full rounded-t-xl h-52"></div>
            <div class="bg-red-500 w-full rounded-t-xl h-64"></div>

        </div>

    </div>

    <!-- ACTIVITY -->
    <div class="bg-white rounded-2xl shadow-lg p-6">

        <h2 class="text-2xl font-bold text-blue-950 mb-6">
            Actividad Reciente
        </h2>

        <div class="space-y-5">

            <div class="flex items-center gap-4">

                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">

                    ✅

                </div>

                <div>

                    <p class="font-semibold">
                        Asistencia registrada
                    </p>

                    <p class="text-sm text-gray-500">
                        Hace 5 minutos
                    </p>

                </div>

            </div>

            <div class="flex items-center gap-4">

                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">

                    👨‍🎓

                </div>

                <div>

                    <p class="font-semibold">
                        Nuevo estudiante
                    </p>

                    <p class="text-sm text-gray-500">
                        Hace 20 minutos
                    </p>

                </div>

            </div>

            <div class="flex items-center gap-4">

                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">

                    📷

                </div>

                <div>

                    <p class="font-semibold">
                        Rostro registrado
                    </p>

                    <p class="text-sm text-gray-500">
                        Hace 1 hora
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

