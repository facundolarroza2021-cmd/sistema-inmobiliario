<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Intenta loguear al usuario y retorna el token y datos.
     * Lanza excepciones si falla para que el controlador sepa qué responder.
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        // 1. Validar Credenciales
        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas'],
            ]);
        }

        // 2. Validar Estado
        if ( $user->activo === false) {
            throw new \Exception('Usuario desactivado. Contacte al administrador.', 403);
        }

        // 3. Generar Token (Limpiando anteriores si deseas sesión única)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    /**
     * Registra un nuevo usuario en el sistema.
     */
    public function registrarUsuario(array $datos): User
    {
        return User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'role' => $datos['role'],
            'activo' => true,
        ]);
    }

    /**
     * Cierra la sesión (invalida el token actual).
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
