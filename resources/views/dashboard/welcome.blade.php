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

            </div>
        </div>

        <!-- Módulos / Accesos Rápidos filtrados por Rol -->
        
    </div>

    </div>
@endsection