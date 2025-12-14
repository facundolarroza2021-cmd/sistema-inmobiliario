<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Iniciar sesión y obtener Token",
     * tags={"Autenticación"},
     *
     * @OA\RequestBody(
     * required=true,
     *
     * @OA\JsonContent(
     * required={"email","password"},
     *
     * @OA\Property(property="email", type="string", format="email", example="admin@test.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123")
     * )
     * ),
     *
     * @OA\Response(
     * response=200,
     * description="Login exitoso",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="message", type="string", example="Login exitoso"),
     * @OA\Property(property="access_token", type="string", example="2|AbCdEf..."),
     * @OA\Property(property="token_type", type="string", example="Bearer"),
     * @OA\Property(
     * property="user",
     * type="object",
     * description="Datos del usuario",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Administrador"),
     * @OA\Property(property="email", type="string", example="admin@test.com"),
     * @OA\Property(property="role", type="string", example="admin"),
     * @OA\Property(property="activo", type="boolean", example=true),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     * )
     * ),
     *
     * @OA\Response(response=401, description="Credenciales incorrectas")
     * )
     */
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
                ...$data, 
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 403);
        }
    }

    /**
     * @OA\Post(
     * path="/api/register",
     * summary="Registrar un nuevo usuario",
     * tags={"Autenticación"},
     *
     * @OA\RequestBody(
     * required=true,
     *
     * @OA\JsonContent(
     * required={"name","email","password"},
     *
     * @OA\Property(property="name", type="string", example="Nuevo Usuario"),
     * @OA\Property(property="email", type="string", format="email", example="usuario@test.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="role", type="string", example="admin")
     * )
     * ),
     *
     * @OA\Response(
     * response=201,
     * description="Usuario registrado exitosamente",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="message", type="string"),
     * @OA\Property(property="user", type="object")
     * )
     * ),
     *
     * @OA\Response(response=422, description="Error de validación (Email duplicado, pass corta, etc)")
     * )
     */
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
            'user' => $user,
        ], 201);
    }

    /**
     * @OA\Post(
     * path="/api/logout",
     * summary="Cerrar sesión (Invalidar Token)",
     * tags={"Autenticación"},
     * security={{"sanctum":{}}},
     *
     * @OA\Response(response=200, description="Logout exitoso")
     * )
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    /**
     * @OA\Get(
     * path="/api/perfil",
     * summary="Obtener datos del usuario logueado",
     * tags={"Autenticación"},
     * security={{"sanctum":{}}},
     *
     * @OA\Response(
     * response=200,
     * description="Datos del usuario",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="id", type="integer"),
     * @OA\Property(property="name", type="string"),
     * @OA\Property(property="email", type="string"),
     * @OA\Property(property="role", type="string")
     * )
     * )
     * )
     */
    public function perfil(Request $request)
    {
        return response()->json($request->user());
    }
}
