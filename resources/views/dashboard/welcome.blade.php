@extends('layouts.app')

@section('title', 'Inicio - Control de Plazas SEIEM')

@section('content')
    <div class="space-y-8">

        <!-- Header de Bienvenida / Tarjeta Principal -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="h-2 bg-gradient-to-r from-[#9B2242] via-[#7B1B34] to-[#B8975A]"></div>
            <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#9B2242]/10 text-[#9B2242] mb-3">
                        <span class="w-2 h-2 rounded-full bg-[#9B2242] animate-pulse"></span>
                        Sistema Activo
                    </span>
                    <!-- Corregido: 'SesNom' coincide con lo asignado en AuthController -->
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">
                        Bienvenido(a), <span
                            class="text-[#9B2242]">{{ session('SesNom', session('SesCta', 'Usuario')) }}</span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-1 max-w-2xl">
                        Panel de administración para el Control de Plazas del Departamento de Registro y Archivo. Seleccione
                        un módulo para comenzar.
                    </p>
                </div>

                <!-- Acceso Directo / Info rápida -->
                <div class="bg-gray-50 border border-gray-200/80 rounded-xl p-4 w-full sm:w-auto min-w-[220px]">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Módulo Actual</p>
                    <p class="text-sm font-semibold text-gray-700 mt-0.5">Gestión de Cargas & Consultas</p>
                    <div class="mt-2 pt-2 border-t border-gray-200 flex justify-between text-xs text-gray-500">
                        <span>SEIEM</span>
                        <span class="font-bold text-[#B8975A]">EdoMéx</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Módulos / Accesos Rápidos filtrados por Rol -->
        <div>
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#9B2242]" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Módulos del Sistema
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- MÓDULOS DE ADMINISTRADOR / ROL 1 --}}
                @if(session('SesUsr') == 1)
                    <!-- Card 1: Carga Analítico -->
                    <a href="{{ route('analitico.index') }}"
                        class="group bg-white rounded-xl p-6 border border-gray-200/80 shadow-sm hover:shadow-md hover:border-[#9B2242] transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div
                                class="w-12 h-12 rounded-lg bg-[#9B2242]/10 text-[#9B2242] group-hover:bg-[#9B2242] group-hover:text-white transition-colors duration-200 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-[#9B2242] transition-colors">
                                Carga Analítico
                            </h4>
                            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                                Módulo para la importación y procesamiento de archivos analíticos de plazas.
                            </p>
                        </div>
                        <div
                            class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-semibold text-[#9B2242]">
                            <span>Acceder al módulo</span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </a>

                    <!-- Card Auditoría / Análisis -->
                    <a href="{{ route('auditoria.index') }}"
                        class="group bg-white rounded-xl p-6 border border-gray-200/80 shadow-sm hover:shadow-md hover:border-[#9B2242] transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div
                                class="w-12 h-12 rounded-lg bg-[#9B2242]/10 text-[#9B2242] group-hover:bg-[#9B2242] group-hover:text-white transition-colors duration-200 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-[#9B2242] transition-colors">
                                Auditoría de Plantillas
                            </h4>
                            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                                Verificación y validación de categorías estructurales y directivas.
                            </p>
                        </div>
                        <div
                            class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-semibold text-[#9B2242]">
                            <span>Acceder al módulo</span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </a>
                @endif

                {{-- MÓDULOS DE CONSULTA / AMBOS ROLES (1 y 2) --}}
                @if(in_array(session('SesUsr'), [1, 2]))
                    <!-- Card: Consulta General -->
                    <a href="{{ route('consulta.general') }}"
                        class="group bg-white rounded-xl p-6 border border-gray-200/80 shadow-sm hover:shadow-md hover:border-[#B8975A] transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div
                                class="w-12 h-12 rounded-lg bg-[#B8975A]/10 text-[#B8975A] group-hover:bg-[#B8975A] group-hover:text-white transition-colors duration-200 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-[#B8975A] transition-colors">
                                Consulta General
                            </h4>
                            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                                Búsqueda y filtrado de la estructura ocupacional y distribución de plazas.
                            </p>
                        </div>
                        <div
                            class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-semibold text-[#B8975A]">
                            <span>Acceder al módulo</span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </a>
                @endif

            </div>
        </div>

    </div>
@endsection