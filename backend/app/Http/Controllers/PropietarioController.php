<?php

namespace App\Http\Controllers;

use App\Services\PropietarioService;
use Illuminate\Http\Request;
use App\DTOs\PropietarioData;
use App\Http\Resources\PropietarioResource;

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
        $propietarios = $this->propietarioService->listarTodos();
        return PropietarioResource::collection($propietarios);
    }

    public function show($id)
    {
        $resultado = $this->propietarioService->obtenerDetalleConMetricas($id);
        
        $propietario = $resultado['datos'];
        $propietario->kpis = $resultado['kpis'];

        return new PropietarioResource($propietario);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'dni'             => 'required|string|max:20|unique:propietarios,dni',
            'email'           => 'required|email|max:255|unique:propietarios,email',
            'telefono'        => 'nullable|string|max:50',
            'cbu'             => 'nullable|string|max:100',
        ]);
        $dto = PropietarioData::fromArray($validated);
        $propietario = $this->propietarioService->crearPropietario($dto);

        return new PropietarioResource($propietario);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string',
            'dni' => 'required|string|unique:propietarios,dni,' . $id,
            'email' => 'required|email|unique:propietarios,email,' . $id,
            'telefono' => 'nullable',
            'cbu' => 'nullable'
        ]);

        $dto = PropietarioData::fromArray($validated);

        $propietario = $this->propietarioService->actualizarPropietario($id, $dto);
        
        return response()->json($propietario);
    }

    public function destroy($id)
    {
        $this->propietarioService->eliminarPropietario($id);
        return response()->json(['message' => 'Propietario eliminado correctamente']);
    }
}
