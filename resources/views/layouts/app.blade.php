<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trim($__env->yieldContent('title')) ? $__env->yieldContent('title').' · ' : '' }}North College</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2">Saltar al contenido</a>

    @include('components.sidebar')

    <div x-show="sidebarOpen" x-transition.opacity x-cloak class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false"></div>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6">
            <button type="button" class="-ml-1 rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = true" aria-label="Abrir menú">
                <x-icon name="bars-3" />
            </button>

            <p class="min-w-0 flex-1 truncate text-sm text-slate-500">
                @hasSection('title')
                    <span class="font-medium text-slate-900">@yield('title')</span>
                @else
                    {{ $header ?? '' }}
                @endif
            </p>

            <div class="flex items-center gap-2">
                <span class="hidden text-right leading-tight sm:block">
                    <span class="block text-sm font-medium text-slate-900">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-slate-500">{{ auth()->user()->role?->name ?? 'Sin rol asignado' }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost" title="Cerrar sesión">
                        <x-icon name="arrow-right-start-on-rectangle" />
                        <span class="hidden md:inline">Salir</span>
                    </button>
                </form>
            </div>
        </header>

        <main id="contenido" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @include('components.alerts')

            @yield('content')

            {{ $slot ?? '' }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
