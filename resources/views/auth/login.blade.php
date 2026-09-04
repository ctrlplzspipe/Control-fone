<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identificación de usuario - Control de Plazas</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- <link href="{{ asset('jQuery/Alerts/jquery.alerts.css') }}" rel="stylesheet" type="text/css" /> -->
    <!-- <script type="text/javascript" src="{{ asset('js/jquery-1.4.2.min.js') }}"></script> -->
    <!-- <script type="text/javascript" src="{{ asset('jQuery/Alerts/jquery.alerts.js') }}"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-slate-100 min-h-screen flex flex-col justify-between font-sans">

    @if (session('error'))
        <script type="text/javascript">
            $(document).ready(function () {
                jAlert("{{ session('error') }}", 'Aviso', function () {
                    window.location.href = "{{ route('login') }}";
                });
            });
        </script>
    @endif

    <!-- Encabezado Ajustado con Imágenes Proporcionadas -->
    <!-- Encabezado -->
    <header class="bg-white border-b border-slate-200 py-4 px-8 shadow-sm">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">

            <!-- Escudo Estado de México -->
            <div class="flex items-center justify-start min-w-[200px]">
                <img src="{{ asset('images/escudo-gob-mex.png') }}" class="h-28 md:h-24 w-auto object-contain"
                    alt="Escudo Estado de México">
            </div>

            <!-- Título Central -->
            <div class="text-center">
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">
                    Control de Plazas
                </h1>

                <p class="text-slate-500 font-medium text-sm md:text-base">
                    Departamento de Registro y Archivo
                </p>
            </div>

            <!-- Logo SEIEM / EDOMÉX -->
            <div class="flex items-center justify-end min-w-[200px]">
                <img src="{{ asset('images/SEIEM.png') }}" class="h-12 md:h-14 w-auto object-contain"
                    alt="SEIEM Estado de México">
            </div>

        </div>
    </header>

    <!-- Tarjeta del Formulario de Login -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 w-full max-w-md p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Identificación de usuario</h2>
                <p class="text-sm text-slate-500">Ingrese sus credenciales para acceder al sistema</p>
            </div>

            <form action="{{ route('login.post') }}" method="post" class="space-y-4">
                @csrf

                <div>
                    <label for="log" class="block text-xs font-semibold text-slate-600 uppercase mb-1">Usuario</label>
                    <input type="text" name="log" id="log" maxlength="10" placeholder="Ingrese usuario" required
                        onchange="this.value = this.value.toUpperCase();"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-slate-800">
                </div>

                <div>
                    <label for="password"
                        class="block text-xs font-semibold text-slate-600 uppercase mb-1">Contraseña</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required
                        onchange="this.value = this.value.toUpperCase();"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-slate-800">
                </div>

                <button type="submit"
                    class="w-full mt-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-slate-800 hover:to-black text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    Ingresar al sistema
                </button>
            </form>
        </div>
    </main>

    <!-- Pie de página -->
    <footer class="text-center py-4 text-xs text-slate-400">
        &copy; {{ date('Y') }} Gobierno del Estado de México - SEIEM
    </footer>

</body>

</html>