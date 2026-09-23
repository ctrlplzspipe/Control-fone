<?php

namespace App\Providers;

use App\Services\MenuService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Comparte el menú del sidebar únicamente con el layout principal
        // (no con "*"), así la consulta se ejecuta una sola vez por
        // request, sin importar cuántas vistas/parciales se rendericen
        // dentro de ese layout.
        View::composer('layouts.app', function ($view) {
            $sidebarMenu = [];

            if (Session::has('valid_user')) {
                $tipoUsuario = is_numeric(Session::get('UsuTip'))
                    ? (int) Session::get('UsuTip')
                    : 1;

                $sidebarMenu = app(MenuService::class)->getMenuParaUsuario($tipoUsuario);
            }

            $view->with('sidebarMenu', $sidebarMenu);
        });
    }
}