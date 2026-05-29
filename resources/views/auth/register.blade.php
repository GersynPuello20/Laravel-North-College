<x-guest-layout>

<div class="min-h-screen flex">

    <div class="hidden lg:flex w-1/2 relative bg-gradient-to-b from-blue-950 to-blue-800 overflow-hidden">

        <div class="absolute inset-0">
            <img src="{{ asset('images/campus.jpg') }}"
                 class="w-full h-full object-cover opacity-20"
                 alt="Campus North College">
        </div>

        <div class="absolute bottom-0 left-0 w-full h-48 bg-red-600 transform -skew-y-6 origin-bottom-left">
        </div>

        <div class="relative z-10 flex flex-col justify-center items-center text-center px-10 text-white">

            <div class="w-28 h-28 rounded-full border-4 border-white flex items-center justify-center mb-6 shadow-2xl bg-white/10 backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-14 w-14"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 14l6.16-3.422A12.083 12.083 0 0112 20.055
                          a12.083 12.083 0 01-6.16-9.477L12 14z" />
                </svg>
            </div>

            <h1 class="text-5xl font-extrabold tracking-wide">
                NORTH COLLEGE
            </h1>

            <div class="w-24 h-1 bg-red-500 rounded-full my-5"></div>

            <p class="text-xl leading-relaxed text-gray-200 max-w-md">
                Sistema de Gestión Académica
                y Control Biométrico
            </p>

            <div class="flex gap-10 mt-16">
                <div class="text-center">
                    <div class="w-14 h-14 rounded-full border border-white flex items-center justify-center mx-auto mb-3">👤</div>
                    <p class="text-sm">Reconocimiento Facial</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 rounded-full border border-white flex items-center justify-center mx-auto mb-3">📊</div>
                    <p class="text-sm">Control de Asistencia</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 rounded-full border border-white flex items-center justify-center mx-auto mb-3">🎓</div>
                    <p class="text-sm">Gestión Académica</p>
                </div>
            </div>

        </div>

    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-100 p-8">

        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-10">

            <div class="text-center mb-8">
                <h2 class="text-4xl font-bold text-blue-950">
                    Crear Cuenta
                </h2>
                <p class="text-gray-500 mt-3">
                    Regístrate para acceder al sistema institucional
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Nombre Completo')" class="text-gray-700 font-semibold"/>
                    <x-text-input id="name"
                                  class="block mt-2 w-full rounded-xl border-gray-300 focus:border-blue-900 focus:ring-blue-900 py-3"
                                  type="text"
                                  name="name"
                                  :value="old('name')"
                                  required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-5">
                    <x-input-label for="email" :value="__('Correo Institucional')" class="text-gray-700 font-semibold"/>
                    <x-text-input id="email"
                                  class="block mt-2 w-full rounded-xl border-gray-300 focus:border-blue-900 focus:ring-blue-900 py-3"
                                  type="email"
                                  name="email"
                                  :value="old('email')"
                                  required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-5">
                    <x-input-label for="password" :value="__('Contraseña')" class="text-gray-700 font-semibold"/>
                    <x-text-input id="password"
                                  class="block mt-2 w-full rounded-xl border-gray-300 focus:border-red-600 focus:ring-red-600 py-3"
                                  type="password"
                                  name="password"
                                  required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-5">
                    <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="text-gray-700 font-semibold"/>
                    <x-text-input id="password_confirmation"
                                  class="block mt-2 w-full rounded-xl border-gray-300 focus:border-red-600 focus:ring-red-600 py-3"
                                  type="password"
                                  name="password_confirmation"
                                  required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit"
                        class="w-full mt-8 bg-blue-900 hover:bg-red-600 transition-all duration-300 text-white py-4 rounded-xl font-bold shadow-lg">
                    Registrarse
                </button>

                <div class="text-center mt-8">
                    <p class="text-gray-500">
                        ¿Ya tienes una cuenta registrada?
                    </p>
                    <a href="{{ route('login') }}"
                       class="mt-4 inline-block border-2 border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition-all duration-300 px-8 py-3 rounded-xl font-semibold">
                        Iniciar Sesión
                    </a>
                </div>

            </form>

        </div>

    </div>

</div>

</x-guest-layout>