@extends('layouts.app')

@section('title', 'Identificación de Usuario - Control de Plazas')

@section('content')
<!-- Contenedor Principal: Centrado en pantalla con fondo vino semitransparente -->
<div class="min-h-[calc(100vh-120px)] w-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-[#9B2242]/15 via-slate-100 to-[#9B2242]/20">

    <!-- Tarjeta Integrada Flotante (Split Screen tipo Card) -->
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 grid grid-cols-1 lg:grid-cols-2">

        <!-- ================================================= -->
        <!-- COLUMNA IZQUIERDA: IMAGEN E INFORMACIÓN INSTITUCIONAL -->
        <!-- ================================================= -->
        <section class="relative hidden lg:flex flex-col items-center justify-between bg-[#9B2242]/5 p-8 xl:p-10 border-r border-gray-100">
            
            <!-- Decoración de fondo suave -->
            <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#9B2242_1px,transparent_1px)] [background-size:16px_16px]"></div>

            <!-- Header / Badge superior de la columna -->
            <div class="relative z-10 w-full flex justify-start">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#9B2242]/10 text-[#9B2242]">
                    <span class="w-2 h-2 rounded-full bg-[#9B2242] animate-pulse"></span>
                    Sistema Institucional
                </span>
            </div>

            <!-- Contenido Central / Imagen de Logotipos -->
            <div class="relative z-10 flex flex-col items-center text-center my-auto space-y-6">
                <div class="p-4 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-100/50 max-w-sm">
                    <img src="{{ asset('images/colibri.webp') }}" 
                         alt="Gobierno del Estado de México" 
                         class="w-full h-auto object-contain">
                </div>

                <div class="max-w-xs space-y-2">
                    <h2 class="text-xl font-black text-gray-800 uppercase tracking-wide">
                        Control de Plazas
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Gestión y Consulta de Estructura Ocupacional del Departamento de Registro y Archivo.
                    </p>
                </div>
            </div>

            <!-- Footer interno de la columna -->
            <div class="relative z-10 w-full text-center pt-4 border-t border-gray-200/50">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                    Estado de México · SEIEM
                </p>
            </div>
        </section>

        <!-- ================================================= -->
        <!-- COLUMNA DERECHA: FORMULARIO DE ACCESO -->
        <!-- ================================================= -->
        <section class="p-6 sm:p-10 xl:p-12 flex flex-col justify-between bg-white">
            
            <div>
                <!-- Encabezado del Formulario -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-[#9B2242]/10 text-[#9B2242] mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">
                        Identificación de Usuario
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500">
                        Ingrese sus credenciales para acceder al sistema.
                    </p>
                </div>

                <!-- Formulario -->
                <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                    @csrf

                    <!-- Input Usuario -->
                    <div>
                        <label for="usuario" class="block mb-1.5 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Usuario
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input id="usuario" type="text" name="log" value="{{ old('log') }}" required autofocus
                                autocomplete="username" placeholder="INGRESE SU USUARIO" 
                                class="w-full pl-11 pr-4 py-3 text-xs sm:text-sm uppercase bg-gray-50/80 border border-gray-200 rounded-xl text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:border-[#9B2242] focus:ring-2 focus:ring-[#9B2242]/20 transition-all">
                        </div>
                    </div>

                    <!-- Input Contraseña -->
                    <div>
                        <label for="password" class="block mb-1.5 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password" placeholder="••••••••" 
                                class="w-full pl-11 pr-4 py-3 text-xs sm:text-sm bg-gray-50/80 border border-gray-200 rounded-xl text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:border-[#9B2242] focus:ring-2 focus:ring-[#9B2242]/20 transition-all">
                        </div>
                    </div>

                    <!-- Botón Ingresar -->
                    <button type="submit" 
                        class="w-full mt-2 py-3.5 px-6 bg-[#9B2242] hover:bg-[#7B1B34] active:bg-[#68162B] text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-[#9B2242]/20 hover:shadow-xl hover:shadow-[#9B2242]/30 transition-all duration-200 flex items-center justify-center gap-2">
                        <span>Ingresar al sistema</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Información Inferior del Formulario -->
            <div class="mt-8 pt-4 border-t border-gray-100 text-center sm:text-left">
                <p class="text-[11px] text-gray-400 font-medium">
                    Departamento de Registro y Archivo SEIEM
                </p>
            </div>

        </section>

    </div>
</div>
@endsection