<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $data = $this->authService->login($request->email, $request->password);
            
            return response()->json([
                'message' => 'Login exitoso',
                ...$data // Expande access_token, user, etc.
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 403);
        }
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,administrativo,cobrador',
        ]);

        $user = $this->authService->registrarUsuario($validated);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user
        ], 201);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function perfil(Request $request)
    {
        return response()->json($request->user());
    }
}