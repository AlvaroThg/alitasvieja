<?php

namespace App\Modules\Branch\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBranch
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario tenga una sucursal activa asignada en sesión.
     * Sucursales disponibles: Cochabamba, Tarija.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TODO: Implementar lógica para verificar sucursal activa en sesión
        // session('branch_id') debe estar definido antes de acceder a rutas protegidas

        return $next($request);
    }
}
