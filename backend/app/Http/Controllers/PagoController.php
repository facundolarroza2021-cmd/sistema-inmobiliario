<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Cuota;
use App\Models\Caja;
use App\Services\PagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de la API para la gestión de pagos.
 * Su función principal es validar la entrada HTTP, inyectar el PagoService 
 * y devolver la respuesta JSON adecuada.
 */

class PagoController extends Controller
{
    protected $pagoService;

    /**
     * Inyección del PagoService en el constructor.
     *
     * @param PagoService $pagoService La instancia del servicio de pagos.
     */

    public function __construct(PagoService $pagoService)
    {
        $this->pagoService = $pagoService;
    }

    /**
     * Registra un pago simple (único) a través de la API.
     *
     * **Ruta:** POST /api/pagos
     * **Permisos:** Cobrador, Administrativo, Admin.
     *
     * @param Request $request Debe contener cuota_id, monto, forma_pago y opcionalmente observacion.
     * @return \Illuminate\Http\JsonResponse Con el mensaje de éxito y los datos de la transacción.
     */

    public function store(Request $request)
    {
        $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto' => 'required|numeric|min:1',
            'forma_pago' => 'required|string',
            'observacion' => 'nullable|string|max:255', // Aseguramos que la validación esté aquí
        ]);

        try {
            $resultado = $this->pagoService->registrarPago(
                $request->cuota_id,
                $request->monto,
                $request->forma_pago,
                $request->observacion ?? null // Pasamos la observación
            );

            return response()->json([
                'message' => 'Pago registrado correctamente',
                'data' => $resultado,
            ]);

        } catch (\Exception $e) { // <-- FIX: Usamos \Exception para el namespace global
            // Mensaje claro en caso de error
            return response()->json(['message' => $e->getMessage()], 400); 
        }
    }
    
    /**
     * Registra un pago total que cubre múltiples cuotas (Cobro Múltiple TPV).
     *
     * **Ruta:** POST /api/pagos/multiple
     * **Permisos:** Cobrador, Administrativo, Admin.
     *
     * @param Request $request Debe contener cuotas_ids[], monto_total, medio_pago y observacion (opcional).
     * @return \Illuminate\Http\JsonResponse Con el mensaje de éxito y los datos de la transacción consolidada.
     */

    public function storeMultiple(Request $request)
    {
        // 1. VALIDACIÓN
        $request->validate([
            'cuotas_ids' => 'required|array|min:1',
            'cuotas_ids.*' => 'exists:cuotas,id',
            'monto_total' => 'required|numeric|min:0.01', 
            'medio_pago' => 'required|string',
            'observacion' => 'nullable|string|max:255',
        ]);

        try {
            // 2. LLAMADA AL SERVICIO
            $resultado = $this->pagoService->registrarPagoMultiple(
                $request->cuotas_ids,
                (float) $request->monto_total, 
                $request->medio_pago,
                $request->observacion
            );

            // 3. RESPUESTA DE ÉXITO
            return response()->json([
                'message' => 'Cobro registrado exitosamente',
                'data' => $resultado 
            ]);

        } catch (\Exception $e) { // <-- FIX: Usamos \Exception para el namespace global
            // 4. MANEJO DE ERROR CLARO
            
            // Loguear el error completo para el desarrollador
            Log::error("Error de negocio/código en PagoController::storeMultiple: " . $e->getMessage(), [
                'payload' => $request->all()
            ]);
            
            // Devolver el mensaje de la excepción directamente al usuario/consola
            return response()->json([
                'message' => $e->getMessage() 
            ], 400);
        }
    }
    
    /**
     * Genera el PDF del recibo para un pago específico.
     *
     * @param int $id El ID del registro de pago (Pago::id).
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse Stream del archivo PDF.
     */
    
    public function generarPdf(int $id)
    {
        $pago = Pago::with('cuota.contrato.inquilino', 'cuota.contrato.propiedad')->findOrFail($id);

        try {
            // Aseguramos que se usa la vista correcta y las variables necesarias
            $data = [
                'pago' => $pago,
                'cuota' => $pago->cuota,
                'contrato' => $pago->cuota->contrato,
                'inquilino' => $pago->cuota->contrato->inquilino,
                'cuotas' => [$pago->cuota] // Para compatibilidad con vistas que esperan un array
            ];
            
            $pdf = Pdf::loadView('pdf.recibo_pago', $data);
            return $pdf->stream('recibo-'.$pago->codigo_comprobante.'.pdf');
        } catch (\Exception $e) {
            Log::error("Error generando PDF para pago ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Error generando PDF: ' . $e->getMessage()], 500);
        }
    }
}