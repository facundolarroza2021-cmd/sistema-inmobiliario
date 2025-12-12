<?php

namespace App\Http\Controllers;

use App\Services\CajaService;
use Illuminate\Http\Request; // Inyectamos el servicio

class CajaController extends Controller
{
    protected $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    public function index(Request $request)
    {
        $movimientos = $this->cajaService->listarMovimientos(
            $request->query('mes'),
            $request->query('anio')
        );

        return response()->json($movimientos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'tipo' => 'required|in:INGRESO,EGRESO',
            'categoria' => 'required|string',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string',
        ]);

        $movimiento = $this->cajaService->registrarMovimiento($validated, $request->user()->id);

        return response()->json($movimiento, 201);
    }

    public function balance(Request $request)
    {
        $balance = $this->cajaService->calcularBalance(
            $request->query('mes'),
            $request->query('anio')
        );

        return response()->json($balance);
    }

    public function destroy($id)
    {
        $this->cajaService->eliminarMovimiento($id);

        return response()->json(['message' => 'Movimiento eliminado']);
    }
}
