@extends('layouts.app')

@section('title', 'Carga de Movimientos de Plazas (MDP)')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Encabezado de la Sección -->
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="space-y-1">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-[#9B2242]/10 text-[#9B2242]">
                    <span class="w-2 h-2 rounded-full bg-[#9B2242] animate-pulse"></span>
                    Módulo FONE / Movssep
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-gray-800 uppercase tracking-wide">
                    Carga de Movimientos de Plazas (MDP)
                </h1>
                <p class="text-xs sm:text-sm text-gray-500">
                    Importación masiva de datos estructurados a partir de un archivo comprimido .ZIP
                </p>
            </div>

            <div
                class="hidden sm:flex items-center justify-center p-3.5 bg-slate-50 rounded-2xl border border-gray-100 text-[#9B2242]">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
        </div>

        <!-- Tarjeta Principal del Formulario -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            <form id="formCargaMdp" class="p-6 sm:p-8 space-y-6">
                @csrf

                <!-- Grid de Campos de Entrada -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Fecha de Proceso -->
                    <div>
                        <label for="lbl_FECHA" class="block mb-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Fecha de Proceso
                        </label>
                        <div class="relative">
                            <input type="date" id="lbl_FECHA" name="lbl_FECHA" value="{{ date('Y-m-d') }}" min="2019-01-01"
                                max="{{ date('Y-m-d') }}" required
                                class="w-full px-4 py-3 text-xs sm:text-sm bg-gray-50/80 border border-gray-200 rounded-xl text-gray-800 font-medium focus:bg-white focus:outline-none focus:border-[#9B2242] focus:ring-2 focus:ring-[#9B2242]/20 transition-all">
                        </div>
                    </div>

                    <!-- Archivo ZIP -->
                    <div class="md:col-span-2">
                        <label for="archivo" class="block mb-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Archivo .TXT (Contenido en .ZIP)
                        </label>
                        <input type="file" id="archivo" name="archivo" accept=".zip" required
                            class="block w-full text-xs text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#9B2242]/10 file:text-[#9B2242] hover:file:bg-[#9B2242]/20 border border-gray-200 rounded-xl bg-gray-50/80 cursor-pointer focus:outline-none transition-all">
                    </div>

                </div>

                <!-- Banner Informativo -->
                <div
                    class="bg-amber-50/80 border border-amber-200/70 rounded-xl p-4 flex items-center gap-3 text-amber-900 text-xs sm:text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <span class="font-bold">Última actualización registrada:</span>
                        <span
                            class="inline-block px-2.5 py-0.5 ml-1 bg-amber-100/80 text-amber-900 font-extrabold rounded-md border border-amber-200/50">
                            {{ $fecha }}
                        </span>
                    </div>
                </div>

                <!-- Status de Procesamiento (Animación de Carga) -->
                <div id="loadDiv" class="hidden p-5 bg-[#9B2242]/5 border border-[#9B2242]/15 rounded-xl space-y-3">
                    <div class="flex items-center justify-between text-xs font-bold text-[#9B2242]">
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-[#9B2242]" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Procesando datos e insertando en la base de datos... Por favor no cierre esta ventana.
                        </span>
                    </div>
                    <div class="w-full bg-gray-200/70 rounded-full h-2 overflow-hidden">
                        <div class="bg-[#9B2242] h-2 rounded-full animate-pulse w-full"></div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="reset" id="btnReset"
                        class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold uppercase tracking-wider rounded-xl transition-all active:scale-95">
                        Limpiar
                    </button>

                    <button type="submit" id="btnSubmit"
                        class="px-6 py-3 bg-[#9B2242] hover:bg-[#7B1B34] active:bg-[#68162B] text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-[#9B2242]/20 hover:shadow-lg hover:shadow-[#9B2242]/30 transition-all flex items-center gap-2 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Cargar Movimientos</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formCargaMdp');
            const loadDiv = document.getElementById('loadDiv');
            const btnSubmit = document.getElementById('btnSubmit');
            const btnReset = document.getElementById('btnReset');

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(form);

                // Mostrar indicador de procesamiento local
                loadDiv.classList.remove('hidden');
                btnSubmit.disabled = true;
                btnReset.disabled = true;
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');

                fetch("{{ route('carga.mdp.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        loadDiv.classList.add('hidden');
                        btnSubmit.disabled = false;
                        btnReset.disabled = false;
                        btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Proceso Finalizado',
                                text: data.message,
                                confirmButtonColor: '#9B2242'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error en la Carga',
                                text: data.message,
                                confirmButtonColor: '#9B2242'
                            });
                        }
                    })
                    .catch(error => {
                        loadDiv.classList.add('hidden');
                        btnSubmit.disabled = false;
                        btnReset.disabled = false;
                        btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');

                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Servidor',
                            text: 'Ocurrió un error inesperado al procesar el archivo masivo.',
                            confirmButtonColor: '#9B2242'
                        });
                    });
            });
        });
    </script>
@endpush