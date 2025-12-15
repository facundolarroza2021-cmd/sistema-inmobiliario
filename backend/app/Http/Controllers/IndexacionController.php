<?php

namespace App\Http\Controllers;

use App\Services\IndexacionService;
use App\Http\Requests\AplicarAjusteRequest; // Se debe crear este Form Request
use Illuminate\Support\Carbon;

/**
 * Controlador de Indexación: Interfaz HTTP para la gestión de ajustes de alquileres.
 */
class IndexacionController extends Controller
{
    protected $indexacionService;

    public function __construct(IndexacionService $indexacionService)
    {
        $this->indexacionService = $indexacionService;
    }

    /**
     * Endpoint para listar contratos activos aptos para ajuste.
     */
    public function index()
    {
        return response()->json(
            $this->indexacionService->listarContratosActivosParaAjuste()
        );
    }

    /**
     * Aplica el ajuste (indexación) al contrato y a sus cuotas futuras.
     *
     * @param AplicarAjusteRequest $request Request validada por FormRequest.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AplicarAjusteRequest $request)
    {
        $validated = $request->validated();

        try {
            $contrato = $this->indexacionService->aplicarAjuste(
                $validated['contrato_id'],
                $validated['porcentaje'] / 100, // Convertir de porcentaje a factor (25% -> 0.25)
                Carbon::parse($validated['fecha_aplicacion']),
                $validated['motivo']
            );

            return response()->json([
                'message' => 'Ajuste aplicado exitosamente.',
                'contrato' => $contrato,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al aplicar el ajuste: ' . $e->getMessage()], 400);
        }
    }
}