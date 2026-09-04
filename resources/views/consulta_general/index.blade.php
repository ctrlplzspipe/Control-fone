@extends('layouts.app') {{-- O la plantilla base que estés utilizando --}}

@section('content')
<div class="container mt-4">
    <div class="row mb-2">
        <div class="col-12">
            <small class="text-muted">En sesión: <strong>{{ Auth::user()->name ?? session('SesNom') }}</strong></small>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-info text-white text-center fw-bold">
            Consulta GENERAL por CURP o PLAZA
        </div>
        
        <form action="{{ route('consulta.general.resultados') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row g-3 justify-content-center">
                    <div class="col-md-4">
                        <label for="optTipo" class="form-label">Elija tipo de búsqueda:</label>
                        <select id="optTipo" name="optTipo" class="form-select" required>
                            <option value="CURP" selected>Por CURP</option>
                            <option value="RFC">Por RFC</option>
                            <option value="CVEPRE">Por CVEPRE SEIEM</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="dato" class="form-label">Información a BUSCAR:</label>
                        <input type="text" id="dato" name="dato" class="form-control" maxlength="24" placeholder="CURP" required />
                    </div>
                </div>

                <div class="row text-center mt-4">
                    <div class="col-12 text-muted small">
                        <strong>Notas:</strong><br />
                        Movimientos FONE fecha: {{ $dateMDP }}<br />
                        Anexo IV Qna: {{ $dateAnexo }}<br />
                        Analítico Qna: {{ $dateAnalitico }}
                    </div>
                </div>
            </div>

            <div class="card-footer text-center bg-light">
                <button type="reset" class="btn btn-secondary me-2">
                    <i class="fa fa-eraser"></i> Borrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Ejecutar consulta
                </button>
            </div>
        </form>
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
</script>
@endsection