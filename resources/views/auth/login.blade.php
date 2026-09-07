@extends('layouts.app')

@section('title', 'Iniciar Sesión - Control de Plazas SEIEM')

@section('content')
    <div class="flex items-center justify-center min-h-[calc(100vh-220px)] py-6 px-4">
        <div
            class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden transition-all duration-300">

            <!-- Franja de gradiente institucional EdoMéx -->
            <div class="h-2.5 bg-gradient-to-r from-[#9B2242] via-[#7B1B34] to-[#B8975A]"></div>

            <div class="p-6 sm:p-10">
                <!-- Encabezado de la Tarjeta -->
                <div class="text-center mb-8">
                    <div
                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#9B2242]/10 text-[#9B2242] mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Identificación de usuario</h2>
                    <p class="text-sm text-gray-500 mt-1">Ingrese sus credenciales para acceder al sistema</p>
                </div>

                <!-- Formulario de Acceso -->
                <form action="{{ route('login') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Campo Usuario (Nombre exacto del backend: log) -->
                    <div>
                        <label for="log" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                            Usuario
                        </label>
                        <div class="relative">
                            <input type="text" id="log" name="log" value="{{ old('log') }}" placeholder="Ingrese su usuario"
                                required autofocus oninput="this.value = this.value.toUpperCase()"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-800 uppercase placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#9B2242] focus:border-transparent transition-all text-sm font-medium">
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="••••••••" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#9B2242] focus:border-transparent transition-all text-sm font-medium">
                        </div>
                    </div>

                    <!-- Botón de Ingreso -->
                    <button type="submit"
                        class="w-full bg-[#9B2242] hover:bg-[#7B1B34] active:bg-[#5C1326] text-white font-bold py-3.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 text-sm tracking-wide flex items-center justify-center gap-2">
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
            <div class="bg-gray-50 border-t border-gray-100 py-3 px-6 text-center">
                <span class="text-xs text-gray-500 font-medium">
                    Departamento de Registro y Archivo SEIEM
                </span>
            </div>
        </div>
    </div>
@endsection