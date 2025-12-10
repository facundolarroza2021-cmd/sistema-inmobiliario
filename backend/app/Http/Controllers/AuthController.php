<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Verificamos usuario y contraseña
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Verificamos si el usuario está activo (campo que vi en tu modelo)
        if ($user->activo === 0 || $user->activo === false) {
             return response()->json([
                'message' => 'Usuario desactivado. Contacte al administrador.'
            ], 403);
        }

        // Crear Token
        // Borramos tokens anteriores para mantener una sola sesión (opcional, pero recomendado)
        $user->tokens()->delete();
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user // Retornamos datos del usuario (rol, nombre, etc.)
        ]);
    }

    // POST /api/register
    // NOTA: Según tus rutas, solo un ADMIN puede ejecutar esto.
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,administrativo,cobrador', // Validamos roles permitidos
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'activo' => true, // Por defecto activo al crear
        ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user
        ], 201);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        // Elimina el token actual que se usó para la petición
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    // GET /api/perfil
    public function perfil(Request $request)
    {
        return response()->json($request->user());
    }
}