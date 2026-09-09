<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Session::has('valid_user')) {
            return redirect()->route('mainframe');
        }

        return view('auth.login');
    }


    public function login(Request $request): RedirectResponse
    {
        $usuario = strtoupper(trim((string) $request->input('log')));
        $password = strtoupper(trim((string) $request->input('password')));

        if (empty($usuario) || empty($password)) {
            return redirect()->route('login')->with('error', 'Por favor ingrese usuario y contraseña.');
        }

        // Consulta con los nombres exactos de la tabla ctusuarios
        $userRow = DB::table('ctusuarios')
            ->where(DB::raw('BINARY ocuenta'), $usuario)
            ->where(DB::raw('BINARY opass'), $password)
            ->first();

        if ($userRow) {
            // Validar si el usuario está activo (si aplica la columna istatus)
            if (isset($userRow->istatus) && strtoupper(trim($userRow->istatus)) !== 'A' && strtoupper(trim($userRow->istatus)) !== '1') {
                return redirect()->route('login')->with('error', 'El usuario se encuentra inactivo.');
            }

            // Armar nombre completo a partir de las columnas reales
            $nombreCompleto = trim(($userRow->nombre ?? '') . ' ' . ($userRow->paterno ?? '') . ' ' . ($userRow->materno ?? ''));

            // Guardar variables de sesión en Laravel
            Session::put('valid_user', true);
            Session::put('SesUsr', $userRow->tipusr ?? 1);   // tipusr define las opciones del menú en mnuopcs_2
            Session::put('SesCta', $userRow->ocuenta);       // Cuenta (ej. CONSULTA, DIANA, ORLANDO)
            Session::put('UsuTip', $userRow->tipusr ?? 1);   // Identificador de rol
            Session::put('SesNom', $nombreCompleto ?: $userRow->ocuenta);

            return redirect()->route('mainframe')
                ->with('login_success', 'Sesion iniciada correctamente como: ' . ($nombreCompleto ?: $userRow->ocuenta));
        }

        return redirect()->route('login')->with('error', 'Usuario o contraseña incorrectos.');
    }

    public function logout(): RedirectResponse
    {
        Session::flush();
        return redirect()->route('login')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}