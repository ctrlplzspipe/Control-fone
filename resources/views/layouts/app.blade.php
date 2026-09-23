<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Control de Plazas - SEIEM')</title>

    <!-- SweetAlert2 para modales de confimacion -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Notificaciones Toastify.js -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    @stack('styles')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes toastProgress {
            from {
                width: 90%;
            }

            to {
                width: 0%;
            }
        }
    </style>
</head>


<body
    class="bg-[#F8FAFC] h-full flex flex-col font-sans text-gray-800 antialiased selection:bg-[#9B2242] selection:text-white overflow-x-hidden">

    <!-- ========================================================= -->
    <!-- HEADER INSTITUCIONAL -->
    <!-- SOLO SE MUESTRA FUERA DEL LOGIN -->
    <!-- ========================================================= -->

    @if(!request()->is('login'))

        <!-- Top Ribbon Tricolor / Institucional -->
        <div class="h-1.5 bg-gradient-to-r from-[#9B2242] via-[#7B1B34] to-[#B8975A] w-full shrink-0"></div>

        <!-- Header Institucional Centrado -->
        <header id="siteHeader"
            class="bg-white/95 backdrop-blur border-b border-gray-200 shadow-xs sticky top-0 z-40 shrink-0">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-2.5 grid grid-cols-3 items-center gap-4">

                <!-- Lado Izquierdo: Botón Sidebar (móvil) + Escudo Gobierno -->
                <div class="flex items-center justify-start gap-3">
                    @if(session('valid_user') && count($sidebarMenu ?? []))
                        <button id="sidebarToggle" type="button"
                            class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-[#9B2242] hover:bg-gray-100 transition-colors shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    @endif

                    <img src="{{ asset('images/escudo-2.png') }}" alt="Gobierno del Estado de México"
                        class="h-10 sm:h-14 md:h-16 lg:h-20 w-auto object-contain shrink-0 max-w-full transition-all">
                </div>

                <!-- Centro: Título y Subtítulo Centrados -->
                <div class="text-center">
                    <h1 class="text-base sm:text-xl md:text-2xl font-black text-gray-800 tracking-tight leading-none">
                        Control de Plazas
                    </h1>
                    <p class="text-[10px] sm:text-xs md:text-sm text-gray-500 font-bold tracking-wide mt-1 leading-tight">
                        Departamento de Registro y Archivo
                    </p>
                </div>

                <!-- Lado Derecho: Logo Estado de México / SEIEM -->
                <div class="flex items-center justify-end">
                    <img src="{{ asset('images/seiem-logo.png') }}" alt="Estado de México"
                        class="h-10 sm:h-12 md:h-14 lg:h-16 w-auto object-contain shrink-0 max-w-full transition-all">
                </div>

            </div>
        </header>

    @endif

    <!-- ========================================================= -->
    <!-- SUB-BARRA DE USUARIO AUTENTICADO -->
    <!-- ========================================================= -->

    @if(session('valid_user'))

        <div id="userSubbar" class="bg-slate-100/80 border-b border-gray-200/80 shadow-xs shrink-0">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-1.5 flex items-center justify-between text-xs">

                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-gray-500 hidden sm:inline">Usuario conectado:</span>
                    <strong class="text-gray-800 font-bold">{{ session('SesNom') }}</strong>
                </div>

                <a href="{{ url('/logout') }}" onclick="confirmarCerrarSesion(event)"
                    class="inline-flex items-center gap-1.5 text-[#9B2242] hover:text-[#7B1B34] font-bold hover:underline transition-colors">
                    <span>Cerrar Sesión</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </a>

            </div>
        </div>

    @endif

    <!-- ========================================================= -->
    <!-- SIDEBAR + CONTENIDO PRINCIPAL -->
    <!-- ========================================================= -->

    <div class="flex flex-1 w-full max-w-[1600px] mx-auto min-h-0 overflow-hidden">

        @if(!request()->is('login'))
            @include('partials.sidebar')
        @endif

        <main class="flex-1 min-w-0 overflow-y-auto {{ request()->is('login') ? 'p-0' : 'px-4 sm:px-6 lg:px-8 py-6' }}">

            <!-- Botón "Regresar al Menú Principal" -->
            @if(
                    !request()->routeIs('dashboard')
                    && !request()->is('dashboard')
                    && !request()->is('login')
                    && !request()->is('/')
                )
                <div class="mb-5">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-[#9B2242] hover:border-[#9B2242]/30 shadow-xs text-xs font-bold transition-all group">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-gray-400 group-hover:text-[#9B2242] transition-colors" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Regresar al Menú Principal</span>
                    </a>
                </div>
            @endif

            @yield('content')

        </main>

    </div>

    <!-- ========================================================= -->
    <!-- FOOTER INSTITUCIONAL -->
    <!-- ========================================================= -->

    <footer class="bg-[#1E293B] text-white text-xs py-3.5 px-4 border-t-4 border-[#B8975A] shrink-0 mt-auto">
        <div class="max-w-[1600px] mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">

            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#B8975A]"></span>
                <p class="font-medium tracking-wide">
                    © {{ date('Y') }} Servicios Educativos Integrados al Estado de México (SEIEM)
                </p>
            </div>

            <p
                class="text-slate-400 text-[11px] font-mono bg-slate-800/80 px-3 py-0.5 rounded-md border border-slate-700/50">
                Sistema de Control de Plazas <span class="text-[#B8975A] font-bold">v2.0</span>
            </p>

        </div>
    </footer>

    <!-- ========================================================= -->
    <!-- OVERLAY GLOBAL DE CARGA -->
    <!-- ========================================================= -->

    <div id="globalLoader"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] hidden flex-col items-center justify-center transition-opacity duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-2xl border border-gray-100 text-center max-w-xs mx-4 space-y-4">
            <div class="inline-flex items-center justify-center p-3 bg-[#9B2242]/10 text-[#9B2242] rounded-full">
                <svg class="animate-spin h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Procesando consulta...</h3>
                <p class="text-xs text-gray-500 mt-1">Por favor espere un momento.</p>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- SCRIPTS GLOBALES -->
    <!-- ========================================================= -->

    <script>
        function notify(mensaje, tipo = 'exito') {
            const estilos = {
                exito: { bg: '#7B1B34', borde: '#B8975A', icono: '✓' },
                error: { bg: '#DC2626', borde: '#7F1D1D', icono: '✕' },
                advertencia: { bg: '#D97706', borde: '#92400E', icono: '⚠' },
                info: { bg: '#2563EB', borde: '#1E3A8A', icono: 'ℹ' },
            };

            const c = estilos[tipo] || estilos.exito;
            const duracion = 1850;
            const node = document.createElement('div');

            node.style.cssText = 'display:flex; align-items:center; gap:10px; position:relative; padding-bottom:6px; min-width:220px;';
            node.innerHTML = `
                <span style="font-size:15px; line-height:1;">${c.icono}</span>
                <span style="flex:1;">${mensaje}</span>
                <div style="position:absolute; left:-16px; right:-16px; bottom:-14px; height:3px; background:rgba(255,255,255,0.3);">
                    <div style="height:100%; width:100%; background:#fff; animation:toastProgress ${duracion}ms linear forwards;"></div>
                </div>
            `;

            Toastify({
                node: node,
                duration: duracion,
                gravity: "top",
                position: "center",
                stopOnFocus: true,
                close: false,
                style: {
                    background: c.bg,
                    color: "#FFFFFF",
                    borderRadius: "0.75rem",
                    fontWeight: "600",
                    fontSize: "13px",
                    boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
                    borderLeft: `5px solid ${c.borde}`,
                    overflow: "hidden",
                }
            }).showToast();
        }

        function confirmarCerrarSesion(event) {
            event.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "¿Estás seguro de que deseas salir del sistema?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#9f1239',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ url('/logout') }}";
                }
            });
        }

        @if(session('success')) notify("{{ session('success') }}", 'exito'); @endif
        @if(session('login_success')) notify("{{ session('login_success') }}", 'exito'); @endif
        @if(session('error')) notify("{{ session('error') }}", 'error'); @endif

        window.showLoader = function() {
            const loader = document.getElementById('globalLoader');
            if (loader) { loader.classList.remove('hidden'); loader.classList.add('flex'); }
        };

        window.hideLoader = function () {
            const loader = document.getElementById('globalLoader');
            if (loader) { loader.classList.add('hidden'); loader.classList.remove('flex'); }
        };

        window.addEventListener('pageshow', function (event) {
            hideLoader();
            if (event.persisted || (typeof window.performance !== 'undefined' && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });

        (function () {
            const toggleBtn = document.getElementById('sidebarToggle');
            const closeBtn = document.getElementById('sidebarClose');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (!sidebar || !overlay) return;

            function abrirSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }

            function cerrarSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }

            if (toggleBtn) toggleBtn.addEventListener('click', abrirSidebar);
            if (closeBtn) closeBtn.addEventListener('click', cerrarSidebar);
            overlay.addEventListener('click', cerrarSidebar);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') cerrarSidebar();
            });
        })();
    </script>

    @stack('scripts')

</body>

</html>