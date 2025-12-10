<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    // El ...$roles permite pasar varios roles permitidos (ej: admin,administrativo)
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Verificamos si hay usuario logueado
        if (! $request->user()) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // 2. Si el usuario es ADMIN, tiene pase libre a todo (Superpoder)
        if ($request->user()->role === 'admin') {
            return $next($request);
        }

        // 3. Verificamos si el rol del usuario está en la lista permitida para esta ruta
        if (in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // 4. Si no coincide, prohibimos el paso
        return response()->json(['message' => 'No tienes permisos para esta acción'], 403);
    }
}