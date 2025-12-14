<?php

namespace App\Policies;

use App\Models\Propiedad;
use App\Models\User;

class InquilinoPolicy
{
    /**
     * MODO DIOS: Se ejecuta antes que cualquier otra regla.
     * Si devuelve true, autoriza TODO automáticamente.
     */
    public function before(User $user, $ability)
    {
        if ($user->email === 'admin@admin.com') {
            return true;
        }
        // Si no es el admin, sigue evaluando las funciones de abajo...
    }

    // Reglas para usuarios normales (si tuvieras empleados en el futuro)
    public function viewAny(User $user): bool
    {
        return true; // Todos pueden ver la lista
    }

    public function view(User $user, Propiedad $propiedad): bool
    {
        return true; // Todos pueden ver el detalle
    }

    public function create(User $user): bool
    {
        return true; // Usuarios logueados pueden crear
    }

    public function update(User $user, Propiedad $propiedad): bool
    {
        // Ejemplo: Solo si es dueño de la propiedad (lógica futura)
        // return $user->id === $propiedad->user_id;
        return false; // Por defecto bloqueado si no es Admin
    }

    public function delete(User $user, Propiedad $propiedad): bool
    {
        return false; // Por defecto bloqueado si no es Admin
    }
}