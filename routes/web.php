<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalizadorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConsultaGeneralController;

// Rutas de Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

//Rutas protegidas por middleware de autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/consulta-general', [ConsultaGeneralController::class, 'index'])->name('consulta.general');
    Route::post('/consulta-general/resultados', [ConsultaGeneralController::class, 'resultados'])->name('consulta.general.resultados');
    Route::post('/consulta-general/data', [ConsultaGeneralController::class, 'data'])->name('consulta.general.data');
});
// Rutas del Sistema Protegido
Route::get('/', [DashboardController::class, 'index'])->name('mainframe');

// Auditoría de Plantillas
Route::get('/auditoria', function () {
    return view('upload');
})->name('auditoria.index');

Route::post('/analizar-plantilla', [AnalizadorController::class, 'procesar'])->name('auditoria.procesar');