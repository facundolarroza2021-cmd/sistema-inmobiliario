<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Verificación de Roles (RBAC).
 *
 * Intercepta las peticiones HTTP entrantes para validar si el usuario autenticado
 * posee los privilegios necesarios para acceder a la ruta solicitada.
 * Implementa una jerarquía donde el rol 'admin' tiene acceso total.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Verificación de Autenticación
        // Se asegura de que el usuario exista en el contexto de la request.
        if (! $request->user()) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // 2. Bypass de Super Administrador
        // Si el usuario es 'admin', se omiten las restricciones de la ruta.
        if ($request->user()->role === 'admin') {
            return $next($request);
        }

        // 3. Validación de Roles Permitidos
        // Verifica si el rol del usuario está dentro de los argumentos permitidos.
        if (in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // 4. Denegacion de acceso
        return response()->json(['message' => 'No tienes permisos para esta acción'], 403);
    }
}
