<?php

namespace App\Http\Controllers;

use App\Services\InquilinoService;
use Illuminate\Http\Request;

class InquilinoController extends Controller
{
    protected $inquilinoService;

    public function __construct(InquilinoService $inquilinoService)
    {
        $this->inquilinoService = $inquilinoService;
    }

    /**
     * @OA\Get(
     * path="/api/inquilinos",
     * summary="Listar todos los inquilinos",
     * tags={"Inquilinos"},
     * security={{"sanctum":{}}},
     *
     * @OA\Response(response=200, description="Lista de inquilinos")
     * )
     */
    public function index()
    {
        return response()->json($this->inquilinoService->listarTodos());
    }

    /**
     * @OA\Post(
     * path="/api/inquilinos",
     * summary="Registrar un nuevo inquilino",
     * tags={"Inquilinos"},
     * security={{"sanctum":{}}},
     *
     * @OA\RequestBody(
     * required=true,
     *
     * @OA\JsonContent(
     * required={"nombre_completo", "dni"},
     *
     * @OA\Property(property="nombre_completo", type="string", example="Carlos Pérez"),
     * @OA\Property(property="dni", type="string", example="30123456"),
     * @OA\Property(property="email", type="string", format="email", example="carlos@mail.com"),
     * @OA\Property(property="telefono", type="string", example="3454123456"),
     * @OA\Property(property="garante_nombre", type="string", example="Padre de Carlos"),
     * @OA\Property(property="garante_dni", type="string", example="20123456")
     * )
     * ),
     *
     * @OA\Response(response=201, description="Inquilino creado")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string',
            'dni' => 'required|string|unique:inquilinos',
            'telefono' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $inquilino = $this->inquilinoService->crearInquilino($validated);

        return response()->json($inquilino, 201);
    }

    /**
     * @OA\Put(
     * path="/api/inquilinos/{id}",
     * summary="Actualizar un inquilino existente",
     * tags={"Inquilinos"},
     * security={{"sanctum":{}}},
     *
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID del inquilino",
     * required=true,
     *
     * @OA\Schema(type="integer")
     * ),
     *
     * @OA\RequestBody(
     * required=true,
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="nombre_completo", type="string"),
     * @OA\Property(property="dni", type="string"),
     * @OA\Property(property="email", type="string"),
     * @OA\Property(property="telefono", type="string")
     * )
     * ),
     *
     * @OA\Response(response=200, description="Inquilino actualizado")
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string',
            'dni' => 'required|string|unique:inquilinos,dni,'.$id,
            'telefono' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $inquilino = $this->inquilinoService->actualizarInquilino($id, $validated);

        return response()->json($inquilino);
    }

    /**
     * @OA\Delete(
     * path="/api/inquilinos/{id}",
     * summary="Eliminar un inquilino",
     * tags={"Inquilinos"},
     * security={{"sanctum":{}}},
     *
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     * @OA\Response(response=200, description="Inquilino eliminado")
     * )
     */
    public function destroy($id)
    {
        $this->inquilinoService->eliminarInquilino($id);

        return response()->json(['message' => 'Inquilino eliminado correctamente']);
    }
}
