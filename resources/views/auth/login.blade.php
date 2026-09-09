@extends('layouts.app')

@section('title', 'Iniciar Sesión - Control de Plazas SEIEM')

@section('content')
    <div class="flex items-center justify-center w-full my-auto py-2">
        <div
            class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden transition-all duration-300">

            <!-- Franja de gradiente institucional EdoMéx -->
            <div class="h-2 bg-gradient-to-r from-[#9B2242] via-[#7B1B34] to-[#B8975A]"></div>

            <div class="p-6 sm:p-7">
                <!-- Encabezado de la Tarjeta -->
                <div class="text-center mb-5">
                    <div
                        class="inline-flex items-center justify-center w-11 h-11 rounded-full bg-[#9B2242]/10 text-[#9B2242] mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight">Identificación de usuario</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Ingrese sus credenciales para acceder al sistema</p>
                </div>

                <!-- Formulario de Acceso -->
                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Campo Usuario -->
                    <div>
                        <label for="log" class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                            Usuario
                        </label>
                        <div class="relative">
                            <input type="text" id="log" name="log" value="{{ old('log') }}" placeholder="Ingrese su usuario"
                                required autofocus oninput="this.value = this.value.toUpperCase()"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 uppercase placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#9B2242] focus:border-transparent transition-all text-sm font-medium">
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <label for="password"
                            class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="••••••••" required
                                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#9B2242] focus:border-transparent transition-all text-sm font-medium">
                        </div>
                    </div>

                    <!-- Botón de Ingreso -->
                    <button type="submit"
                        class="w-full bg-[#9B2242] hover:bg-[#7B1B34] active:bg-[#5C1326] text-white font-bold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 text-sm tracking-wide flex items-center justify-center gap-2 mt-2">
                        <span>Ingresar al sistema</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Pie de la Tarjeta -->
            <div class="bg-gray-50 border-t border-gray-100 py-2.5 px-6 text-center">
                <span class="text-[11px] text-gray-500 font-medium">
                    Departamento de Registro y Archivo SEIEM
                </span>
            </div>
        </div>
    </div>
@endsection