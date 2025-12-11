<?php

namespace App\Http\Controllers;

use App\Services\LiquidacionService;
use Illuminate\Http\Request;

class LiquidacionController extends Controller
{
    protected $liquidacionService;

    public function __construct(LiquidacionService $liquidacionService)
    {
        $this->liquidacionService = $liquidacionService;
    }

    public function index()
    {
        return response()->json($this->liquidacionService->listarLiquidaciones());
    }

    public function store(Request $request)
    {
        $request->validate([
            'propietario_id' => 'required|exists:propietarios,id',
            'periodo' => 'required|date_format:Y-m' // "2025-01"
        ]);

        try {
            $liquidacion = $this->liquidacionService->generarLiquidacion(
                $request->propietario_id,
                $request->periodo
            );

            return response()->json([
                'message' => 'Liquidación generada exitosamente',
                'data' => $liquidacion
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}