<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
/**
 * Class User
 * * Representa a los usuarios del sistema (Admin, Empleados e Inquilinos con acceso).
 * Utiliza Laravel Sanctum para autenticación por API.
 *
 * @property int $id Identificador único del usuario.
 * @property string $name Nombre completo.
 * @property string $email Correo electrónico (usado para login).
 * @property string $password Hash de la contraseña.
 * @property string $role Rol del usuario: 'admin', 'administrativo', 'cobrador', 'tenant'.
 * @property boolean $activo Indica si el usuario tiene permitido el acceso (1=Sí, 0=No).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * * @package App\Models
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'activo',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
