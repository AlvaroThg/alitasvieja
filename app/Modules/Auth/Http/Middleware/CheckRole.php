<?php

namespace App\Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario autenticado tenga alguno de los roles permitidos.
     * Los roles válidos son: admin, cajero, mesero, cocina.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // TODO: Implementar lógica de verificación de roles
        // Ejemplo de uso en rutas: ->middleware('role:admin,cajero')

        return $next($request);
    }
}
