<?php

namespace App\Http\Controllers;

use App\Services\IndexacionService;
use App\Http\Requests\AplicarAjusteRequest;
use Illuminate\Http\Request; // Necesario para la validación manual de previsualización
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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
     * Endpoint original para listar contratos activos aptos para ajuste. (GET /indexacion)
     */
    public function index()
    {
        return response()->json(
            $this->indexacionService->listarContratosActivosParaAjuste()
        );
    }
    
    /**
     * Endpoint para PREVISUALIZAR los contratos y cuotas afectadas por un ajuste masivo. (POST /indexacion/previsualizar)
     * * @param Request $request Petición con criterios de ajuste.
     * @return \Illuminate\Http\JsonResponse
     */
    public function previsualizar(Request $request)
    {
        // Validar manualmente los criterios del filtro/ajuste
        $validated = $request->validate([
            'tipoAjuste' => 'required|in:porcentaje,monto_fijo',
            'valorAjuste' => 'required|numeric|min:0.01', 
            'fechaAplicacion' => 'required|date_format:Y-m-d', 
        ]);
        
        try {
            $contratosAjustables = $this->indexacionService->previsualizarAjuste($validated);

            return response()->json([
                'message' => 'Previsualización generada exitosamente.',
                'contratos' => $contratosAjustables,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al previsualizar: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Endpoint para APLICAR el ajuste masivo a los contratos seleccionados. (POST /indexacion/aplicar)
     * Nota: Se renombra de 'store' a 'aplicar' para mayor claridad.
     *
     * @param AplicarAjusteRequest $request Request validada por FormRequest.
     * @return \Illuminate\Http\JsonResponse
     */
    public function aplicar(AplicarAjusteRequest $request)
    {
        // El FormRequest ya validó: contratos_ids, tipoAjuste, valorAjuste, fechaAplicacion.
        $validated = $request->validated();

        try {
            $contratosAjustadosCount = $this->indexacionService->aplicarAjusteMasivo(
                $validated['contratos_ids'],
                $validated['tipoAjuste'],
                $validated['valorAjuste'],
                $validated['fechaAplicacion']
            );

            return response()->json([
                'message' => "Ajuste masivo aplicado exitosamente a {$contratosAjustadosCount} contratos.",
                'total_ajustados' => $contratosAjustadosCount,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error en Indexación Masiva: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]); // <-- REGISTRAR EL ERROR COMPLETO
            
            // Devolvemos el 500 para indicarle al frontend que fue un fallo interno
            return response()->json([
                'message' => 'Error interno al aplicar el ajuste. Consulte los logs para el error: ' . $e->getMessage() // <-- Mensaje detallado para debug
            ], 500);
        }
    }
}