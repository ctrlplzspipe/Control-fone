<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (!Session::has('valid_user')) {
            return redirect()->route('login');
        }

        $tipoUsuario = is_numeric(Session::get('UsuTip')) ? (int) Session::get('UsuTip') : 1;
        $cuentaUsuario = strtoupper(trim((string) Session::get('SesCta')));

        // 1. Obtener opciones del menú principal desde la base de datos
        $menuPrincipal = DB::select(
            "SELECT * FROM mnuopcs_2 WHERE tu_id = ? AND estatus = 'ACTIVO' ORDER BY mnu_orden",
            [$tipoUsuario]
        );

        // 2. Mapear submenús para cada opción
        foreach ($menuPrincipal as $menu) {
            if ($menu->mnu_submenu != 0) {
                $menu->submenus = DB::select(
                    "SELECT * FROM submnuopcs_2 WHERE tu_id = ? AND estatus = 'ACTIVO' ORDER BY mnu_orden",
                    [$menu->mnu_submenu]
                );
            } else {
                $menu->submenus = [];
            }
        }

        // 3. Regla especial para usuarios autorizados (ej. DIANA, ORLANDO)
        $usuariosPrecarga = ['DIANA', 'ORLANDO'];
        $tienePrecarga = in_array($cuentaUsuario, $usuariosPrecarga, true);

        return view('dashboard.welcome', compact('menuPrincipal', 'tienePrecarga'));
    }
}