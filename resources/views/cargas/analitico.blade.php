@extends('layouts.app')

@section('title', 'Carga de ANALITICO SEIEM - Control de Plazas')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Tarjeta Principal -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Barra Superior Institucional -->
            <div class="h-1.5 bg-gradient-to-r from-[#9B2242] via-[#7B1B34] to-[#B8975A]"></div>

            <div class="p-6 sm:p-8">
                <!-- Encabezado de la Sección -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-5 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-[#9B2242]/10 rounded-xl text-[#9B2242]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 tracking-tight">
                                Carga Datos de ANALITICO SEIEM
                            </h1>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                                Módulo de actualización masiva de la estructura analítica.
                            </p>
                        </div>
                    </div>

                    <div>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#B8975A]/10 text-[#B8975A] border border-[#B8975A]/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Última QNA Cargada: <strong class="text-gray-800 ml-1">{{ $date }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Formulario -->
                <form id="formCargaAnalitico" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Campo Quincena -->
                        <div>
                            <label for="quincena"
                                class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Quincena (AAAAQQ) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="quincena" id="quincena" maxlength="6" placeholder="Ej. 202614"
                                    pattern="^20[0-9]{2}(0[1-9]|1[0-9]|2[0-4])$" required
                                    class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl p-3 focus:ring-2 focus:ring-[#9B2242] focus:border-[#9B2242] outline-none transition-all font-mono">
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Formato: Año (4 dígitos) + Quincena (01 a 24)</p>
                        </div>

                        <!-- Campo Archivo ZIP -->
                        <div>
                            <label for="archivo"
                                class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Archivo LIS (.ZIP) <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="archivo" id="archivo" accept=".zip,application/zip" required
                                class="w-full text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-xs file:font-semibold file:bg-[#9B2242]/10 file:text-[#9B2242] hover:file:bg-[#9B2242]/20 transition-all">
                            <p class="text-[11px] text-gray-400 mt-1.5">Selecciona únicamente archivos comprimidos en
                                formato .ZIP</p>
                        </div>

                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <button type="reset" id="btnReset"
                            class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold text-xs transition-colors flex items-center gap-2 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Limpiar
                        </button>

                        <button type="submit" id="btnSubmit"
                            class="px-6 py-2.5 rounded-xl bg-[#9B2242] hover:bg-[#7B1B34] text-white font-bold text-xs shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span>Cargar ANALITICO</span>
                        </button>
                    </div>
                </form>

                <!-- Spinner / Indicador de Carga -->
                <div id="loadDiv"
                    class="hidden mt-6 p-5 bg-gradient-to-r from-gray-50 to-red-50/30 border border-gray-200 rounded-2xl text-center space-y-3">
                    <div
                        class="inline-flex items-center justify-center p-3 bg-white rounded-full shadow-sm text-[#9B2242] mb-1">
                        <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">
                        Espere por favor, procesando y descomprimiendo el archivo...
                    </p>
                    <p class="text-xs text-gray-500 max-w-md mx-auto">
                        Este proceso puede tomar un par de minutos según el tamaño del archivo. Por favor no cierre ni
                        recargue la página.
                    </p>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formCargaAnalitico');
            const loadDiv = document.getElementById('loadDiv');
            const btnSubmit = document.getElementById('btnSubmit');

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(form);

                loadDiv.classList.remove('hidden');
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');

                fetch("{{ route('analitico.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        loadDiv.classList.add('hidden');
                        btnSubmit.disabled = false;
                        btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');

                        if (data.success) {
                            Swal.fire({
                                title: 'Proceso terminado',
                                text: data.mensaje || 'La carga se realizó correctamente.',
                                icon: 'success',
                                confirmButtonColor: '#9B2242',
                                confirmButtonText: 'Continuar'
                            }).then(() => {
                                form.reset();
                            });
                        } else {
                            if (data.lineas_error) {
                                Swal.fire({
                                    title: 'Proceso terminado con observaciones',
                                    text: 'Existen líneas con error. Presione OK para descargar el reporte.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#9B2242',
                                    cancelButtonColor: '#6B7280',
                                    confirmButtonText: 'Descargar Errores',
                                    cancelButtonText: 'Cancelar'
                                }).then((result) => {
                                    if (result.isConfirmed && data.url_error) {
                                        window.location.href = data.url_error;
                                    }
                                    form.reset();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error en el proceso',
                                    text: data.mensaje || 'Ocurrió un error al procesar.',
                                    icon: 'error',
                                    confirmButtonColor: '#9B2242'
                                });
                            }
                        }
                    })
                    .catch(error => {
                        loadDiv.classList.add('hidden');
                        btnSubmit.disabled = false;
                        btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Surgió un inconveniente en el servidor durante la transferencia.',
                            icon: 'error',
                            confirmButtonColor: '#9B2242'
                        });
                    });
            });
        });
    </script>
@endpush