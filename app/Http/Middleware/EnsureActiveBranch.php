<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Asegura que el usuario tenga una sucursal activa en sesión.
 * - Owner: puede no tenerla (ve el dashboard global) o elegir una.
 * - Resto de roles: se fuerza su branch_id.
 */
class EnsureActiveBranch
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isOwner()) {
            // Los empleados siempre operan en su propia sucursal
            session(['active_branch_id' => $user->branch_id]);
        }

        return $next($request);
    }
}
