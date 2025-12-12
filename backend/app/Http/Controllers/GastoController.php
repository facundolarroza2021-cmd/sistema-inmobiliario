<?php

namespace App\Http\Controllers;

use App\Services\GastoService;
use Illuminate\Http\Request;

class GastoController extends Controller
{
    protected $gastoService;

    public function __construct(GastoService $gastoService)
    {
        $this->gastoService = $gastoService;
    }

    public function byPropiedad($id)
    {
        return response()->json($this->gastoService->listarPorPropiedad($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'concepto' => 'required|string',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'responsable' => 'required|string|in:PROPIETARIO,INQUILINO,INMOBILIARIA',
        ]);

        $gasto = $this->gastoService->crearGasto($validated);

        return response()->json($gasto, 201);
    }

    public function destroy($id)
    {
        try {
            $this->gastoService->eliminarGasto($id);

            return response()->json(['message' => 'Gasto eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
