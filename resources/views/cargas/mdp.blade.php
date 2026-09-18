@extends('layouts.app')

@section('title', 'Carga de Movimientos de Plazas (MDP)')

@section('content')
<div class="min-h-[calc(100vh-120px)] w-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-[#9B2242]/10 via-slate-50 to-[#9B2242]/15">

    <div class="w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
        
        <!-- Encabezado Vino -->
        <div class="bg-gradient-to-r from-[#9B2242] to-[#7B1B34] p-6 sm:p-8 text-white relative">
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-rose-100 mb-2 backdrop-blur-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Módulo FONE / Movssep
                    </span>
                    <h1 class="text-xl sm:text-2xl font-black uppercase tracking-wide">
                        Carga de Movimientos de Plazas (MDP)
                    </h1>
                    <p class="text-xs sm:text-sm text-rose-100/80 mt-1">
                        Importación masiva de datos estructurados desde archivo ZIP
                    </p>
                </div>
                <div class="hidden sm:flex p-3.5 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/10">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Formulario de Carga -->
        <form id="formCargaMdp" class="p-6 sm:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Select/Input Fecha -->
                <div>
                    <label for="lbl_FECHA" class="block mb-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Fecha de Proceso
                    </label>
                    <input type="date" id="lbl_FECHA" name="lbl_FECHA" 
                        value="{{ date('Y-m-d') }}" min="2019-01-01" max="{{ date('Y-m-d') }}" required
                        class="w-full px-4 py-3 text-xs sm:text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:bg-white focus:outline-none focus:border-[#9B2242] focus:ring-2 focus:ring-[#9B2242]/20 transition-all">
                </div>

                <!-- Input Archivo ZIP -->
                <div class="md:col-span-2">
                    <label for="archivo" class="block mb-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Archivo .TXT (Contenido en .ZIP)
                    </label>
                    <input type="file" id="archivo" name="archivo" accept=".zip" required
                        class="block w-full text-xs text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#9B2242]/10 file:text-[#9B2242] hover:file:bg-[#9B2242]/20 border border-gray-200 rounded-xl bg-gray-50 cursor-pointer focus:outline-none transition-all">
                </div>
            </div>

            <!-- Banner Informativo -->
            <div class="bg-amber-50/80 border border-amber-200/60 rounded-2xl p-4 flex items-center gap-3 text-amber-900 text-xs sm:text-sm">
                <svg class="w-5 h-5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <strong>Última actualización registrada:</strong> 
                    <span class="inline-block px-2 py-0.5 ml-1 bg-amber-100 font-bold rounded-md text-amber-800">{{ $fecha }}</span>
                </div>
            </div>

            <!-- Status de Procesamiento (Animación) -->
            <div id="loadDiv" class="hidden p-5 bg-rose-50/50 border border-rose-100 rounded-2xl space-y-3">
                <div class="flex items-center justify-between text-xs font-bold text-[#9B2242]">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-[#9B2242]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando datos e insertando en MySQL, por favor no cierre esta pantalla...
                    </span>
                </div>
                <div class="w-full bg-rose-200/60 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#9B2242] h-2 rounded-full animate-pulse w-full"></div>
                </div>
            </div>

            <!-- Controles / Botones -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="reset" id="btnReset"
                    class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold uppercase rounded-xl transition-all">
                    Borrar
                </button>
                <button type="submit" id="btnSubmit"
                    class="px-6 py-3 bg-[#9B2242] hover:bg-[#7B1B34] active:bg-[#68162B] text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-[#9B2242]/20 hover:shadow-xl hover:shadow-[#9B2242]/30 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Cargar Movimientos</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formCargaMdp');
    const loadDiv = document.getElementById('loadDiv');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnReset = document.getElementById('btnReset');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        loadDiv.classList.remove('hidden');
        btnSubmit.disabled = true;
        btnReset.disabled = true;

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

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Proceso terminado',
                    text: data.message,
                    confirmButtonColor: '#9B2242'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la carga',
                    text: data.message,
                    confirmButtonColor: '#9B2242'
                });
            }
        })
        .catch(error => {
            loadDiv.classList.add('hidden');
            btnSubmit.disabled = false;
            btnReset.disabled = false;

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
@endsection