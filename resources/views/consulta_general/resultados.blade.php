@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="card text-center shadow-sm">
        <div class="card-header fw-bold">
            Consulta GENERAL {{ $campo }}: {{ $dato }}
        </div>

        <div id="barra" class="progress my-2" style="height: 20px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 100%">
                Espere cargando datos...
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered compact nowrap w-100" id="iddatatable">
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
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Scripts para DataTables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function () {
    var campo = @json($campo);
    var dato = @json($dato);
    
    var titleF = "ConsultaGRAL_" + new Date().toISOString().slice(0,10) + "_" + new Date().getTime();
    var url = "{{ route('consulta.general.data') }}?campo=" + campo + "&dato=" + dato;

    $('#iddatatable').DataTable({
        "initComplete": function () {
            $('#barra').hide();
        },
        "order": [[0, "asc"]],
        "pageLength": 50,
        "dom": 'lBfrtip',
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
                text: '<i class="fa fa-arrow-left"></i> Regresar',
                className: "btn btn-secondary",
                action: function () {
                    window.location.href = "{{ route('consulta.general') }}";
                }
            },
            {
                extend: 'excel',
                title: titleF,
                className: "btn btn-success",
                text: 'Excel <i class="fa fa-table"></i>'
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
        ]
    });
});
</script>
@endsection