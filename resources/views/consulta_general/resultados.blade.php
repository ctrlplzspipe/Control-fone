@extends('layouts.app')

@section('title', 'Resultados de Consulta General - Control de Plazas SEIEM')

@push('styles')
    <!-- DataTables CSS y Botones -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <style>
        /* Personalización DataTables para Tailwind */
        .dataTables_wrapper {
            padding: 1rem 0;
            font-size: 0.8125rem;
        }

        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        table.dataTable thead th {
            background-color: #1e293b !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            padding: 0.75rem 0.85rem !important;
            border-bottom: 2px solid #9B2242 !important;
            white-space: nowrap;
        }

        table.dataTable tbody tr {
            background-color: #ffffff !important;
            transition: background-color 0.15s ease;
        }

        table.dataTable tbody tr:hover {
            background-color: #f8fafc !important;
        }

        table.dataTable tbody td {
            padding: 0.65rem 0.85rem !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap;
        }

        /* Estilo de los Botones del DataTable */
        .dt-buttons {
            display: flex !important;
            gap: 0.5rem !important;
            margin-bottom: 1rem !important;
        }

        .btn-dt-back {
            background-color: #64748b !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 0.5rem !important;
            padding: 0.45rem 1rem !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }

        .btn-dt-back:hover {
            background-color: #475569 !important;
        }

        .btn-dt-excel {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 0.5rem !important;
            padding: 0.45rem 1rem !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }

        .btn-dt-excel:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important;
        }

        .dataTables_filter input {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 0.35rem 0.75rem !important;
            outline: none !important;
        }

        .dataTables_filter input:focus {
            border-color: #9B2242 !important;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6">

        <!-- Widget de Re-búsqueda Rápida Superior -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#9B2242]" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Re-búsqueda Rápida</span>
                </h2>
                <span class="text-[11px] text-gray-400">Realiza una nueva consulta directamente</span>
            </div>

            <form action="{{ route('consulta.general.resultados') }}" method="POST"
                class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                @csrf
                <!-- Selector de Campo -->
                <div class="sm:col-span-4">
                    <label for="campo" class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Buscar por:</label>
                    <select name="campo" id="campo" required
                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#9B2242] focus:border-transparent outline-none bg-white font-medium">
                        <option value="RFC" {{ $campo === 'RFC' ? 'selected' : '' }}>RFC</option>
                        <option value="CURP" {{ $campo === 'CURP' ? 'selected' : '' }}>CURP</option>
                        <option value="NOMBRE" {{ $campo === 'NOMBRE' ? 'selected' : '' }}>Nombre / Apellidos</option>
                        <option value="CCT" {{ $campo === 'CCT' ? 'selected' : '' }}>Clave CCT / FCT</option>
                        <option value="CVEPRE" {{ $campo === 'CVEPRE' ? 'selected' : '' }}>Clave Presupuestal (CVEPRE)
                        </option>
                    </select>
                </div>

                <!-- Input del Dato -->
                <div class="sm:col-span-5">
                    <label for="dato" class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Término de
                        Búsqueda:</label>
                    <input type="text" name="dato" id="dato" value="{{ $dato }}" required
                        placeholder="Ingrese el parámetro..."
                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#9B2242] focus:border-transparent outline-none uppercase font-mono">
                </div>

                <!-- Botón de Búsqueda -->
                <div class="sm:col-span-3">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#9B2242] hover:bg-[#7B1B34] text-white text-xs font-bold rounded-xl shadow-xs transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Consultar</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Contenedor Principal de la Tabla -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-[#9B2242] via-[#7B1B34] to-[#B8975A]"></div>

            <div class="p-6 sm:p-8">
                <!-- Header de Resultados -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4 mb-6">
                    <div>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#B8975A]/10 text-[#B8975A] mb-2">
                            Resultado de Búsqueda
                        </span>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 tracking-tight">
                            Consulta General {{ $campo }}: <span class="text-[#9B2242] font-mono">{{ $dato }}</span>
                        </h1>
                    </div>
                </div>

                <!-- Indicador de Carga Bar Style -->
                <div id="barra" class="mb-6">
                    <div class="w-full bg-gray-100 rounded-xl overflow-hidden p-1 border border-gray-200">
                        <div
                            class="bg-[#9B2242] text-white text-xs font-bold py-1.5 px-3 rounded-lg text-center animate-pulse flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Espere, cargando datos desde la base de datos...
                        </div>
                    </div>
                </div>

                <!-- Contenedor Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-700 border-collapse" id="iddatatable">
                        <thead>
                            <tr>
                                <th>Fuente</th>
                                <th>QNA_AFEC</th>
                                <th>OPERACION</th>
                                <th>COD_SEP</th>
                                <th>CURP</th>
                                <th>CVEPRE</th>
                                <th>NS</th>
                                <th>CCT</th>
                                <th>RFC</th>
                                <th>AP_PAT</th>
                                <th>AP_MAT</th>
                                <th>NOMBRE</th>
                                <th>FECHA_INI</th>
                                <th>FECHA_FIN</th>
                                <th>CPZA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Carga dinámica AJAX -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function () {
            var campo = @json($campo);
            var dato = @json($dato);

            var titleF = "ConsultaGRAL_" + new Date().toISOString().slice(0, 10) + "_" + new Date().getTime();
            var url = "{{ route('consulta.general.data') }}?campo=" + campo + "&dato=" + dato;

            $('#iddatatable').DataTable({
                "initComplete": function () {
                    $('#barra').hide();
                },
                "order": [[0, "asc"]],
                "pageLength": 50,
                "dom": '<"flex flex-col sm:flex-row items-center justify-between gap-4 mb-4"lBf>rt<"flex flex-col sm:flex-row items-center justify-between gap-4 mt-4"ip>',
                "ajax": {
                    "method": "POST",
                    "url": url,
                    "headers": {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                "columnDefs": [
                    { "type": "string", "targets": [5] }
                ],
                "buttons": [
                    {
                        text: '← Regresar',
                        className: "btn-dt-back",
                        action: function () {
                            window.location.href = "{{ route('consulta.general') }}";
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        title: titleF,
                        className: "btn-dt-excel",
                        text: '📊 Descargar Excel'
                    }
                ],
                "columns": [
                    { "data": "FUENTE" },
                    { "data": "QNA_AFEC" },
                    { "data": "OPERACION" },
                    { "data": "COD_SEP" },
                    { "data": "CURP" },
                    { "data": "CVEPRE" },
                    { "data": "NS" },
                    { "data": "CCT" },
                    { "data": "RFC" },
                    { "data": "AP_PAT" },
                    { "data": "AP_MAT" },
                    { "data": "NOMBRE" },
                    { "data": "FECHA_INI" },
                    { "data": "FECHA_FIN" },
                    { "data": "CPZA" }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
        });
    </script>
@endpush