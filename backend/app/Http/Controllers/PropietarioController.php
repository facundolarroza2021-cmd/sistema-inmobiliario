<?php

namespace App\Http\Controllers;

use App\Services\PropietarioService;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 * path="/api/propietarios",
 * summary="Listar todos los propietarios",
 * tags={"Propietarios"},
 *
 * @OA\Response(
 * response=200,
 * description="Operación exitosa"
 * ),
 * @OA\Response(
 * response=401,
 * description="No autorizado"
 * )
 * )
 */
class PropietarioController extends Controller
{
    protected $propietarioService;

    public function __construct(PropietarioService $propietarioService)
    {
        $this->propietarioService = $propietarioService;
    }

    public function index()
    {
        return response()->json($this->propietarioService->listarTodos());
    }

    public function show($id)
    {
        return response()->json($this->propietarioService->obtenerDetalleConMetricas($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string',
            'dni' => 'required|string|unique:propietarios',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string',
            'cbu' => 'nullable|string',
        ]);

        $propietario = $this->propietarioService->crearPropietario($validated);

        return response()->json($propietario, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string',
            'dni' => 'required|string|unique:propietarios,dni,'.$id,
            'email' => 'required|email|unique:propietarios,email,'.$id,
            'telefono' => 'nullable',
            'cbu' => 'nullable',
        ]);

        $propietario = $this->propietarioService->actualizarPropietario($id, $validated);

        return response()->json($propietario);
    }

    public function destroy($id)
    {
        $this->propietarioService->eliminarPropietario($id);

        return response()->json(['message' => 'Propietario eliminado correctamente']);
    }
}
