@extends('layouts.app')

@section('title', 'Consulta General - Control de Plazas SEIEM')

@section('content')
    <div class="space-y-6">

        <!-- Tarjeta Principal del Formulario -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-[#9B2242] via-[#7B1B34] to-[#B8975A]"></div>

            <div class="p-6 sm:p-8">
                <!-- Header de Sección -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-[#9B2242]/10 rounded-xl text-[#9B2242]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 tracking-tight">
                                Consulta General por CURP, RFC o PLAZA
                            </h1>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                                Seleccione el parámetro de búsqueda e ingrese la clave correspondiente para consultar el
                                historial.
                            </p>
                        </div>
                    </div>

                    <div class="hidden sm:block text-right text-xs text-gray-400">
                        En sesión: <span
                            class="font-bold text-gray-700">{{ Auth::user()->name ?? session('SesNom') }}</span>
                    </div>
                </div>

                <!-- Formulario -->
                <form action="{{ route('consulta.general.resultados') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                        <!-- Selector de Tipo -->
                        <div class="md:col-span-4">
                            <label for="optTipo"
                                class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Elija tipo de búsqueda:
                            </label>
                            <div class="relative">
                                <select id="optTipo" name="optTipo" required
                                    class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-3 focus:ring-2 focus:ring-[#9B2242] focus:border-[#9B2242] outline-none transition-all cursor-pointer font-medium">
                                    <option value="CURP" selected>Por CURP</option>
                                    <option value="RFC">Por RFC</option>
                                    <option value="CVEPRE">Por CVEPRE SEIEM</option>
                                </select>
                            </div>
                        </div>

                        <!-- Input de Búsqueda -->
                        <div class="md:col-span-8">
                            <label for="dato" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Información a BUSCAR:
                            </label>
                            <input type="text" id="dato" name="dato" maxlength="24" placeholder="CURP" required
                                class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-3 focus:ring-2 focus:ring-[#9B2242] focus:border-[#9B2242] outline-none transition-all uppercase placeholder-gray-400 font-mono" />
                        </div>
                    </div>

                    <!-- Bloque de Notas e Información de Fuentes -->
                    <div
                        class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-gray-500">
                        <div class="flex items-center gap-2 font-bold text-gray-700 uppercase tracking-wider">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#B8975A]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Fechas de Corte de Información
                        </div>
                        <div class="flex flex-wrap gap-4 text-xs font-medium text-gray-600">
                            <span>Movimientos FONE: <strong class="text-gray-800">{{ $dateMDP }}</strong></span>
                            <span>•</span>
                            <span>Anexo IV Qna: <strong class="text-gray-800">{{ $dateAnexo }}</strong></span>
                            <span>•</span>
                            <span>Analítico Qna: <strong class="text-gray-800">{{ $dateAnalitico }}</strong></span>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="reset"
                            class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold text-xs transition-colors flex items-center gap-2 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Borrar
                        </button>

                        <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#9B2242] hover:bg-[#7B1B34] text-white font-bold text-xs shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Ejecutar Consulta
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectTipo = document.getElementById('optTipo');
            const inputDato = document.getElementById('dato');

            selectTipo.addEventListener('change', function () {
                if (this.value === 'CURP') {
                    inputDato.placeholder = 'CURP';
                } else if (this.value === 'RFC') {
                    inputDato.placeholder = 'RFC';
                } else {
                    inputDato.placeholder = 'CVEPRE SEIEM';
                }
            });
        });

        window.addEventListener('pageshow', function (event) {
            var modalCarga = document.getElementById('loadingModal');
            if (modalCarga) {
                modalCarga.classList.add('hidden');
            }
        })
    </script>

@endsection