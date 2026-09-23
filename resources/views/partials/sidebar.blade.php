{{-- ========================================================= --}}
{{-- SIDEBAR CON ACORDEÓN --}}
{{-- Fijo en escritorio (lg+), panel deslizante (off-canvas) en móvil --}}
{{-- ========================================================= --}}

@if(session('valid_user') && count($sidebarMenu ?? []))

    <!-- Overlay para cerrar el sidebar en móvil -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden"></div>

    <aside id="sidebar" class="fixed lg:sticky inset-y-0 lg:inset-y-auto left-0 lg:top-0 z-50 lg:z-30
                w-72 shrink-0
                bg-white border-r border-gray-200
                transform -translate-x-full lg:translate-x-0
                transition-transform duration-300 ease-in-out
                overflow-y-auto
                flex flex-col">

        <!-- Encabezado del sidebar (solo visible en móvil, para cerrar) -->
        <div class="lg:hidden flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <span class="text-xs font-black uppercase tracking-wider text-[#9B2242]">Módulos</span>
            <button id="sidebarClose" type="button"
                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Acceso directo al dashboard -->
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-2.5 px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-gray-700 hover:text-[#9B2242] hover:bg-gray-50 border-b border-gray-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#B8975A]" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Menú Principal
        </a>

        <!-- Categorías (acordeón) -->
        <nav class="flex-1 py-2">
            @foreach($sidebarMenu as $categoria)
                @php
                    $tieneSubmenus = count($categoria->submenus ?? []) > 0;
                    // ¿Alguno de los submódulos de esta categoría es la página activa?
                    $categoriaActiva = $tieneSubmenus
                        ? collect($categoria->submenus)->contains(fn($s) => $s->ruta && request()->routeIs($s->ruta))
                        : ($categoria->ruta && request()->routeIs($categoria->ruta));
                @endphp

                <div class="px-2">
                    @if($tieneSubmenus)
                        <!-- Categoría con submenús: botón que expande/colapsa -->
                        <button type="button" class="sidebar-cat-btn w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wide text-gray-600 hover:bg-gray-50 hover:text-[#9B2242] transition-colors
                                            {{ $categoriaActiva ? 'bg-[#9B2242]/5 text-[#9B2242]' : '' }}"
                            aria-expanded="{{ $categoriaActiva ? 'true' : 'false' }}">
                            <span class="flex items-center gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $categoria->icono }}" />
                                </svg>
                                <span class="text-left">{{ $categoria->mnu_descripcion }}</span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="sidebar-chevron h-3.5 w-3.5 shrink-0 transition-transform duration-200 {{ $categoriaActiva ? 'rotate-180' : '' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="sidebar-cat-panel overflow-hidden {{ $categoriaActiva ? '' : 'hidden' }}">
                            <ul class="ml-4 pl-4 border-l border-gray-100 my-1 space-y-0.5">
                                @foreach($categoria->submenus as $sub)
                                    <li>
                                        @if($sub->ruta)
                                            <a href="{{ route($sub->ruta) }}" class="block px-3 py-2 rounded-lg text-xs font-medium transition-colors
                                                                        {{ request()->routeIs($sub->ruta)
                                            ? 'bg-[#9B2242] text-white'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-[#9B2242]' }}">
                                                {{ $sub->mnu_descripcion }}
                                            </a>
                                        @else
                                            <span
                                                class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-gray-300 cursor-not-allowed"
                                                title="Este módulo todavía no está disponible en el sistema nuevo">
                                                {{ $sub->mnu_descripcion }}
                                                <span
                                                    class="ml-2 text-[9px] font-bold uppercase tracking-wide bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded">
                                                    Próx.
                                                </span>
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <!-- Categoría sin submenús: link directo (o "Próximamente" si no está migrado) -->
                        @if($categoria->ruta)
                            <a href="{{ route($categoria->ruta) }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wide transition-colors
                                                    {{ request()->routeIs($categoria->ruta)
                                    ? 'bg-[#9B2242] text-white'
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-[#9B2242]' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $categoria->icono }}" />
                                </svg>
                                {{ $categoria->mnu_descripcion }}
                            </a>
                        @else
                            <span
                                class="flex items-center justify-between gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wide text-gray-300 cursor-not-allowed"
                                title="Este módulo todavía no está disponible en el sistema nuevo">
                                <span class="flex items-center gap-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="{{ $categoria->icono }}" />
                                    </svg>
                                    {{ $categoria->mnu_descripcion }}
                                </span>
                                <span
                                    class="text-[9px] font-bold uppercase tracking-wide bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded">
                                    Próx.
                                </span>
                            </span>
                        @endif
                    @endif
                </div>
            @endforeach
        </nav>
    </aside>

    <script>
        // Acordeón del sidebar: una categoría abierta a la vez.
        // Off-canvas en móvil con overlay + botón hamburguesa (definido en app.blade.php).
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.sidebar-cat-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const panel = btn.nextElementSibling;
                    const chevron = btn.querySelector('.sidebar-chevron');
                    const estaAbierto = !panel.classList.contains('hidden');

                    // Cerrar todas las demás categorías
                    document.querySelectorAll('.sidebar-cat-panel').forEach(function (p) {
                        p.classList.add('hidden');
                    });
                    document.querySelectorAll('.sidebar-chevron').forEach(function (c) {
                        c.classList.remove('rotate-180');
                    });

                    // Abrir la actual si estaba cerrada
                    if (!estaAbierto) {
                        panel.classList.remove('hidden');
                        chevron.classList.add('rotate-180');
                    }
                });
            });
        });
    </script>
@endif