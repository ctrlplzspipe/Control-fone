<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema de Control de Plazas')</title>

    <link href="{{ asset('css/format-commun.css') }}" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>

<body class="bg-slate-100 min-h-screen flex flex-col">

    <!-- Encabezado (Equivalente a Encabezado.php) -->
    <header class="bg-white shadow border-b border-slate-200 p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/escudo_5.png') }}" class="h-10" alt="Escudo">
                <div>
                    <h1 class="font-bold text-slate-800 text-lg leading-tight">Control de Plazas</h1>
                    <p class="text-xs text-slate-500">Departamento de Registro y Archivo</p>
                </div>
            </div>

            <div class="text-right text-sm text-slate-600">
                Usuario: <strong class="text-slate-800">{{ session('SesNom', session('SesCta')) }}</strong> |
                <a href="{{ route('logout') }}" class="text-red-600 hover:underline font-semibold ml-2">Cerrar
                    Sesión</a>
            </div>
        </div>
    </header>

    <!-- Cuerpo Principal (Equivalente al FRAMESET central) -->
    <div class="flex flex-1 max-w-7xl w-full mx-auto my-4 gap-4 px-4">

        <!-- Menú Lateral (Equivalente a Menu.php) -->
        <!-- Menú Lateral Dinámico -->
        <aside class="w-64 bg-white rounded-xl shadow-sm p-4 h-fit border border-slate-200">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Menú Principal</h2>

            <nav class="space-y-1">
                @if (isset($menuPrincipal))
                    @foreach ($menuPrincipal as $menu)
                        @if ($menu->mnu_submenu == 0)
                            <!-- Opción Simple -->
                            <a href="{{ $menu->mnu_pagina }}"
                                class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg">
                                {{ $menu->mnu_descripcion }}
                            </a>
                        @else
                            <!-- Opción con Submenú -->
                            <div class="space-y-1">
                                <span class="block px-3 py-2 text-sm font-bold text-slate-800 bg-slate-50 rounded-lg">
                                    {{ $menu->mnu_descripcion }}
                                </span>
                                <div class="pl-4 space-y-1 border-l-2 border-slate-200 ml-2">
                                    @foreach ($menu->submenus as $sub)
                                        <a href="{{ $sub->mnu_pagina }}"
                                            class="block px-3 py-1.5 text-xs text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-md">
                                            {{ $sub->mnu_descripcion }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif

                <!-- Módulo Especial de Precarga (DIANA / ORLANDO) -->
                @if (!empty($tienePrecarga))
                    <div class="space-y-1 pt-2">
                        <span class="block px-3 py-2 text-sm font-bold text-amber-800 bg-amber-50 rounded-lg">
                            Precarga Especial
                        </span>
                        <div class="pl-4 space-y-1 border-l-2 border-amber-200 ml-2">
                            <a href="precarga_ocupada.php"
                                class="block px-3 py-1.5 text-xs text-slate-600 hover:text-amber-700 hover:bg-amber-100 rounded-md">
                                Ocupada
                            </a>
                            <a href="precarga_prefoliosmap.php"
                                class="block px-3 py-1.5 text-xs text-slate-600 hover:text-amber-700 hover:bg-amber-100 rounded-md">
                                Prefoliosmap
                            </a>
                        </div>
                    </div>
                @endif

                <hr class="my-2 border-slate-200">

                <!-- Opción Salir -->
                <a href="{{ route('logout') }}"
                    class="block px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg">
                    Salir del sistema
                </a>
            </nav>
        </aside>

        <!-- Contenido Dinámico (Equivalente a FBienvenida.php) -->
        <main class="flex-1 bg-white rounded-xl shadow-sm p-6 border border-slate-200">
            @yield('content')
        </main>
    </div>

    <!-- Pie de Página (Equivalente a Pie.php) -->
    <footer class="bg-slate-800 text-slate-400 text-center text-xs py-3 mt-auto">
        &copy; {{ date('Y') }} Gobierno del Estado de México - SEIEM. Todos los derechos reservados.
    </footer>

    @stack('scripts')
</body>

</html>