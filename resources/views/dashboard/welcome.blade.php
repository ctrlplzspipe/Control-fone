@extends('layouts.app')

@section('title', 'Bienvenido - Control de Plazas')

@section('content')
    <div class="space-y-4">
        <h2 class="text-2xl font-bold text-slate-800">Bienvenido al Sistema</h2>
        <p class="text-slate-600">
            Selecciona una opción del menú lateral para comenzar a trabajar.
        </p>

        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-md">
            <p class="text-sm text-indigo-700 font-medium">
                Sesión iniciada correctamente como: <strong>{{ session('SesNom') }}</strong> ({{ session('SesCta') }})
            </p>
        </div>
    </div>
@endsection