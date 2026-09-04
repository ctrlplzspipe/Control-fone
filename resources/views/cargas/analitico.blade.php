@extends('layouts.app')

@section('title', 'Carga de ANALITICO SEIEM')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Encabezado de la Sección -->
        <div class="border-b border-slate-200 pb-4 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Carga Datos de ANALITICO SEIEM</h2>
                <p class="text-xs text-slate-500">Módulo de actualización masiva de la estructura analítica.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                Última QNA Cargada: {{ $date }}
            </span>
        </div>

        <!-- Tarjeta del Formulario -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form id="formCargaAnalitico" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Campo Quincena -->
                    <div>
                        <label for="quincena" class="block text-sm font-medium text-slate-700 mb-1">
                            Quincena (AAAAQQ) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="quincena" id="quincena" maxlength="6" placeholder="Ej. 202614"
                            pattern="^20[0-9]{2}(0[1-9]|1[0-9]|2[0-4])$" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <p class="text-[11px] text-slate-400 mt-1">Formato: Año (4 dígitos) + Quincena (01 a 24)</p>
                    </div>

                    <!-- Campo Archivo ZIP -->
                    <div>
                        <label for="archivo" class="block text-sm font-medium text-slate-700 mb-1">
                            Archivo LIS (.ZIP) <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="archivo" id="archivo" accept=".zip,application/zip" required
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-300 rounded-lg cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-1">Selecciona únicamente archivos comprimidos en formato
                            .ZIP</p>
                    </div>

                </div>

                <!-- Botones de Acción -->
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="reset" id="btnReset"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Limpiar
                    </button>
                    <button type="submit" id="btnSubmit"
                        class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition flex items-center gap-2">
                        <span>Cargar ANALITICO</span>
                    </button>
                </div>
            </form>

            <!-- Spinner / Indicador de Carga -->
            <div id="loadDiv"
                class="hidden mt-6 p-4 bg-indigo-50 border border-indigo-100 rounded-lg text-center space-y-3">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-600 border-t-transparent">
                </div>
                <p class="text-sm font-medium text-indigo-900">Espere por favor, procesando y descomprimiendo el archivo...
                </p>
                <p class="text-xs text-indigo-600">Este proceso puede tomar un par de minutos según el tamaño del archivo.
                </p>
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
                                    confirmButtonText: 'Descargar Errores'
                                }).then((result) => {
                                    if (result.isConfirmed && data.url_error) {
                                        window.location.href = data.url_error;
                                    }
                                    form.reset();
                                });
                            } else {
                                Swal.fire('Error en el proceso', data.mensaje || 'Ocurrió un error al procesar.', 'error');
                            }
                        }
                    })
                    .catch(error => {
                        loadDiv.classList.add('hidden');
                        btnSubmit.disabled = false;
                        btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                        console.error('Error:', error);
                        Swal.fire('Error', 'Surgió un inconveniente en el servidor durante la transferencia.', 'error');
                    });
            });
        });
    </script>
@endpush