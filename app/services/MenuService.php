<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class MenuService
{
    /**
     * Mapa de la página del sistema viejo (columna mnu_pagina en
     * mnuopcs_2 / submnuopcs_2) al nombre de ruta ya migrada en Laravel.
     *
     * Conforme se vaya migrando cada submódulo, se agrega aquí su
     * entrada. Lo que no esté en este mapa se muestra en el sidebar
     * como "Próximamente" (deshabilitado), en vez de romper o
     * apuntar a una ruta que no existe.
     */
    private const RUTA_POR_PAGINA_VIEJA = [
        'LoadPlazasMDP.php' => 'carga.mdp.index',
        'LoadPlazasMAP.php' => 'carga.map.index',
        'consulta-general' => 'consulta.general',
        'analitico-seiem' => 'analitico.index',
    ];

    /**
     * Obtiene el menú principal con sus submenús para un tipo de
     * usuario dado, ya resuelto a rutas de Laravel donde sea posible.
     */
    public function getMenuParaUsuario(int $tipoUsuario): array
    {
        $menuPrincipal = DB::select(
            "SELECT * FROM mnuopcs_2 WHERE tu_id = ? AND estatus = 'ACTIVO' ORDER BY mnu_orden",
            [$tipoUsuario]
        );

        foreach ($menuPrincipal as $menu) {
            $menu->icono = $this->iconoParaCategoria($menu->mnu_descripcion);
            $menu->submenus = [];

            if ($menu->mnu_submenu != 0) {
                $submenus = DB::select(
                    "SELECT * FROM submnuopcs_2 WHERE tu_id = ? AND estatus = 'ACTIVO' ORDER BY mnu_orden",
                    [$menu->mnu_submenu]
                );

                foreach ($submenus as $sub) {
                    $sub->ruta = $this->resolverRuta($sub->mnu_pagina);
                }

                $menu->submenus = $submenus;
                $menu->ruta = null;
            } else {
                // Sin submenú: el propio renglón es un link directo
                $menu->ruta = $this->resolverRuta($menu->mnu_pagina);
            }
        }

        return $menuPrincipal;
    }

    private function resolverRuta(?string $paginaVieja): ?string
    {
        if (!$paginaVieja) {
            return null;
        }

        // Comparación insensible a mayúsculas/minúsculas: en la BD real
        // hay inconsistencias como "LoadPlazasMDP.php" vs "LoadPlazasMAP.PHP".
        $paginaVieja = strtolower(trim($paginaVieja));

        $mapaNormalizado = array_change_key_case(self::RUTA_POR_PAGINA_VIEJA, CASE_LOWER);

        $nombreRuta = $mapaNormalizado[$paginaVieja] ?? null;

        // Seguridad extra: si el mapa quedó desactualizado y la ruta
        // ya no existe, se trata igual que "no migrado" en vez de
        // tronar con un error de ruta inexistente.
        if ($nombreRuta && !Route::has($nombreRuta)) {
            return null;
        }

        return $nombreRuta;
    }

    /**
     * Ícono (heroicons, mismo estilo que ya usas en el resto del
     * sistema) según el nombre de la categoría. Si no hay coincidencia,
     * regresa un ícono de carpeta genérico.
     */
    private function iconoParaCategoria(string $descripcion): string
    {
        $desc = strtoupper(trim($descripcion));

        $iconos = [
            'CARGAS FONE' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
            'CONSULTA GRAL' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
            'CONSULTAS FONE' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'CONSULTAS SEIEM' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'CONSULTA NORMATIVA' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'MODULO MAP' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
            'ANEXO IV' => 'M9 17v-2a4 4 0 014-4h4m0 0l-4-4m4 4l-4 4m-9 5h.01M6 21h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z',
            'ANEXO VI' => 'M9 17v-2a4 4 0 014-4h4m0 0l-4-4m4 4l-4 4m-9 5h.01M6 21h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z',
            'VALIDA EN FONE' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'DATA' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
            'TOOLS' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            'VACANCIA' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5',
            'FOLIOS MSP/AP' => 'M9 17v-2a4 4 0 014-4h4m0 0l-4-4m4 4l-4 4m-9 5h.01M6 21h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z',
            'USUARIOS' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 110 8 4 4 0 010-8zm6 3a4 4 0 11-8 0',
        ];

        return $iconos[$desc] ?? 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z';
    }
}