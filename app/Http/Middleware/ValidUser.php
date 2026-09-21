<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('valid_user')) {
            return redirect()->route('login');
        }

        $response = $next($request);

        // Evita que el navegador guarde en caché (incluyendo el caché de
        // atrás/adelante) las páginas protegidas. Sin esto, el navegador
        // puede volver a mostrar una página ya cargada sin pedirla de
        // nuevo al servidor, y por lo tanto sin pasar por este middleware.
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT ');
    }
}