@extends('layouts.app')

@section('title', 'Auditoría de Plantillas - Control de Plazas SEIEM')

@push('styles')
    <!-- DataTables CSS con Botones -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <style>
        /* Personalización de DataTables alineada al sistema SEIEM */
        .dataTables_wrapper {
            padding: 1rem 0;
            font-size: 0.875rem;
        }

        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        table.dataTable thead th {
            background-color: #1e293b !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            padding: 0.75rem 1rem !important;
            border-bottom: 2px solid #9B2242 !important;
        }

        table.dataTable tbody tr {
            background-color: #ffffff !important;
            transition: background-color 0.15s ease-in-out;
        }

        table.dataTable tbody tr:hover {
            background-color: #f8fafc !important;
        }

        table.dataTable tbody td {
            padding: 0.75rem 1rem !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        /* Botón de Excel DataTables personalizado con estética institucional */
        .dt-button {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            transition: all 0.2s ease !important;
        }

        .dt-button:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-1px);
        }

        .dataTables_filter input {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 0.4rem 0.75rem !important;
            outline: none !important;
        }

        .dataTables_filter input:focus {
            border-color: #9B2242 !important;
            ring: 2px #9B2242 !important;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6">

        <!-- Tarjeta del Formulario de Carga -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-[#9B2242] via-[#7B1B34] to-[#B8975A]"></div>

            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-[#9B2242]/10 rounded-lg text-[#9B2242]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Auditoría de Plantillas
                            Ocupacionales</h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Sube los archivos Excel para analizar las
                            discrepancias y visualizar los resultados en tiempo real.</p>
                    </div>
                </div>

                <form id="formAnalizador" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    @csrf

                    <!-- Input Fuente de Datos -->
                    <div>
                        <label for="archivoF" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Fuente de Datos <span class="text-[#9B2242] font-normal">(.xlsx, .xls)</span>
                        </label>
                        <div class="relative">
                            <input type="file" name="archivoF" id="archivoF" required accept=".xlsx, .xls" class="block w-full text-xs text-gray-500
                                file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0
                                file:text-xs file:font-semibold file:bg-[#9B2242]/10 file:text-[#9B2242]
                                hover:file:bg-[#9B2242]/20 file:transition-colors file:cursor-pointer
                                border border-gray-200 rounded-xl p-1.5 focus:outline-none focus:border-[#9B2242]">
                        </div>
                    </div>

                    <!-- Input Estructura Ocupacional -->
                    <div>
                        <label for="archivoP" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Estructura Ocupacional <span class="text-[#9B2242] font-normal">(.xlsx, .xls)</span>
                        </label>
                        <div class="relative">
                            <input type="file" name="archivoP" id="archivoP" required accept=".xlsx, .xls" class="block w-full text-xs text-gray-500
                                file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0
                                file:text-xs file:font-semibold file:bg-[#9B2242]/10 file:text-[#9B2242]
                                hover:file:bg-[#9B2242]/20 file:transition-colors file:cursor-pointer
                                border border-gray-200 rounded-xl p-1.5 focus:outline-none focus:border-[#9B2242]">
                        </div>
                    </div>

                    <!-- Botón de Procesar -->
                    <div>
                        <button type="submit" id="btnProcesar"
                            class="w-full bg-[#9B2242] hover:bg-[#7B1B34] text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all duration-200 flex justify-center items-center gap-2 text-sm cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Procesar y Analizar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Indicador de Carga (Spinner) -->
        <div id="cargando" class="hidden bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
            <div
                class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-[#9B2242] border-t-transparent mb-3">
            </div>
            <p class="text-gray-700 font-semibold text-sm">Analizando plantillas y calculando discrepancias...</p>
            <p class="text-xs text-gray-400 mt-1">Por favor espere, este proceso puede tomar un par de minutos según el
                volumen de datos.</p>
        </div>

        <!-- Contenedor de la Tabla de Resultados -->
        <div id="contenedorTabla" class="hidden bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#B8975A]" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Resultados del Análisis
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table id="tablaResultados" class="w-full text-left text-xs text-gray-700 border-collapse">
                    <thead>
                        <tr>
                            <th>CT</th>
                            <th>Tipo CCT</th>
                            <th>Existentes</th>
                            <th>Cat. Existentes</th>
                            <th>Faltantes</th>
                            <th>Cat. Faltantes</th>
                            <th>No Deberían Existir</th>
                            <th>Cat. No Deberían</th>
                            <th>Excedencias</th>
                            <th>Excedencias Detalle</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Se llena dinámicamente por DataTables -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <!-- Scripts de jQuery, JSZip, DataTables y Botones -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function () {
            let dataTableInstance = null;

            $('#formAnalizador').on('submit', function (e) {
                e.preventDefault();

                let formData = new FormData(this);

                $('#cargando').removeClass('hidden');
                $('#contenedorTabla').addClass('hidden');
                $('#btnProcesar').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

                fetch('/analizar-plantilla', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en el servidor al procesar las plantillas.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        $('#cargando').addClass('hidden');
                        $('#contenedorTabla').removeClass('hidden');
                        $('#btnProcesar').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');

                        // Destruir tabla anterior si existía
                        if (dataTableInstance !== null) {
                            dataTableInstance.destroy();
                        }

                        // Inicializar DataTable con los nuevos datos
                        dataTableInstance = $('#tablaResultados').DataTable({
                            data: data,
                            dom: 'Bfrtip',
                            buttons: [
                                {
                                    extend: 'excelHtml5',
                                    text: '📊 Descargar Reporte en Excel',
                                    title: 'Reporte_Auditoria_Plantillas'
                                }
                            ],
                            columns: [
                                { data: 'CT' },
                                { data: 'TIPO_CCT' },
                                { data: 'Cant_Existentes' },
                                { data: 'Categorias_Existentes' },
                                { data: 'Cant_Faltantes' },
                                { data: 'Categorias_Faltantes' },
                                { data: 'Cant_No_Deberian_Existir' },
                                { data: 'Categorias_No_Deberian_Existir' },
                                { data: 'Cant_Excedencias' },
                                { data: 'Excedencias' },
                                {
                                    data: 'Estado',
                                    render: function (data) {
                                        if (data === 'OK') {
                                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">CORRECTO</span>';
                                        } else {
                                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">CON PROBLEMAS</span>';
                                        }
                                    }
                                }
                            ],
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 10,
                            order: [[0, 'asc']]
                        });
                    })
                    .catch(error => {
                        $('#cargando').addClass('hidden');
                        $('#btnProcesar').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                        alert('Ocurrió un error al procesar el archivo: ' + error.message);
                    });
            });
        });
    </script>
@endpush