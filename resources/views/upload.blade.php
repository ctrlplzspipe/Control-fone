<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría de Plantillas - ControlFone</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- DataTables CSS con Botones -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <style>
        /* Ajustes estéticos para DataTables con Tailwind */
        .dataTables_wrapper { padding: 1.5rem 0; }
        table.dataTable tbody tr { background-color: #fff; }
        table.dataTable tbody tr:hover { background-color: #f8fafc; }
        .dt-button {
            background-color: #059669 !important;
            color: white !important;
            border: none !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 1rem !important;
            font-weight: 600 !important;
        }
        .dt-button:hover {
            background-color: #047857 !important;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-6">

    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Tarjeta del Formulario -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold text-slate-800 mb-1">Auditoría de Plantillas Ocupacionales</h1>
            <p class="text-slate-500 text-sm mb-6">Sube los archivos Excel para analizar las discrepancias y visualizar los resultados en tiempo real.</p>

            <form id="formAnalizador" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Fuente de Datos (.xlsx)</label>
                    <input type="file" name="archivoF" id="archivoF" required accept=".xlsx, .xls"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-lg p-1">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estructura Ocupacional (.xlsx)</label>
                    <input type="file" name="archivoP" id="archivoP" required accept=".xlsx, .xls"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-lg p-1">
                </div>

                <div>
                    <button type="submit" id="btnProcesar"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition duration-200 flex justify-center items-center gap-2">
                        <span>Procesar y Analizar</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Indicador de Carga (Spinner) -->
        <div id="cargando" class="hidden bg-white p-8 rounded-xl shadow-md text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-600 border-t-transparent mb-2"></div>
            <p class="text-slate-600 font-medium">Analizando plantillas y calculando discrepancias...</p>
        </div>

        <!-- Contenedor de la Tabla -->
        <div id="contenedorTabla" class="hidden bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-xl font-bold text-slate-800 mb-4">Resultados del Análisis</h2>
            <div class="overflow-x-auto">
                <table id="tablaResultados" class="w-full text-left text-sm text-slate-700 border-collapse">
                    <thead>
                        <tr class="bg-slate-800 text-white">
                            <th class="p-3">CT</th>
                            <th class="p-3">Tipo CCT</th>
                            <th class="p-3">Existentes</th>
                            <th class="p-3">Cat. Existentes</th>
                            <th class="p-3">Faltantes</th>
                            <th class="p-3">Cat. Faltantes</th>
                            <th class="p-3">No Deberían Existir</th>
                            <th class="p-3">Cat. No Deberían</th>
                            <th class="p-3">Excedencias</th>
                            <th class="p-3">Excedencias Detalle</th>
                            <th class="p-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Se llena dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Scripts de jQuery, JSZip, DataTables y Botones -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            let dataTableInstance = null;

            $('#formAnalizador').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $('#cargando').removeClass('hidden');
                $('#contenedorTabla').addClass('hidden');
                $('#btnProcesar').prop('disabled', true).addClass('opacity-50');

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
                    $('#btnProcesar').prop('disabled', false).removeClass('opacity-50');

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
                                render: function(data) {
                                    if (data === 'OK') {
                                        return '<span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded">CORRECTO</span>';
                                    } else {
                                        return '<span class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded">CON PROBLEMAS</span>';
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
                    $('#btnProcesar').prop('disabled', false).removeClass('opacity-50');
                    alert('Ocurrió un error al procesar el archivo: ' + error.message);
                });
            });
        });
    </script>
</body>
</html>