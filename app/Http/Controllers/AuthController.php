<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse|Response
    {
        if (Session::has('valid_user')) {
            return redirect()->route('mainframe');
        }

        // Retornamos la vista con las cabeceras anti-caché aplicadas
        $response = response()->view('auth.login');
        return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    public function login(Request $request): RedirectResponse
    {
        $usuario = strtoupper(trim((string) $request->input('log')));
        $password = strtoupper(trim((string) $request->input('password')));

        if (empty($usuario) || empty($password)) {
            return redirect()->route('login')->with('error', 'Por favor ingrese usuario y contraseña.');
        }

        $userRow = DB::table('ctusuarios')
            ->where(DB::raw('BINARY ocuenta'), $usuario)
            ->where(DB::raw('BINARY opass'), $password)
            ->first();

        if ($userRow) {
            if (isset($userRow->istatus) && strtoupper(trim($userRow->istatus)) !== 'A' && strtoupper(trim($userRow->istatus)) !== '1') {
                return redirect()->route('login')->with('error', 'El usuario se encuentra inactivo.');
            }

            $nombreCompleto = trim(($userRow->nombre ?? '') . ' ' . ($userRow->paterno ?? '') . ' ' . ($userRow->materno ?? ''));

            // Regenerar el ID de sesión para prevenir Session Fixation y limpiar residuos
            $request->session()->regenerate();

            Session::put('valid_user', true);
            Session::put('SesUsr', $userRow->tipusr ?? 1);
            Session::put('SesCta', $userRow->ocuenta);
            Session::put('UsuTip', $userRow->tipusr ?? 1);
            Session::put('SesNom', $nombreCompleto ?: $userRow->ocuenta);

            return redirect()->route('mainframe')
                ->with('login_success', 'Sesión iniciada correctamente como: ' . ($nombreCompleto ?: $userRow->ocuenta));
        }

        return redirect()->route('login')->with('error', 'Usuario o contraseña incorrectos.');
    }

    public function logout(Request $request): RedirectResponse
    {
        // Limpia los datos de sesión, destruye la cookie y destruye la sesión en servidor
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}