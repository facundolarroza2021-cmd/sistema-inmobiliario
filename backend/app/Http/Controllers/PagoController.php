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

class PagoController extends Controller
{
    protected $pagoService;

    public function __construct(PagoService $pagoService)
    {
        $this->pagoService = $pagoService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto' => 'required|numeric|min:1',
            'forma_pago' => 'required|string',
        ]);

        try {
            $resultado = $this->pagoService->registrarPago(
                $request->cuota_id,
                $request->monto,
                $request->forma_pago
            );

            return response()->json([
                'mensaje' => 'Pago registrado correctamente',
                'detalle' => $resultado,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function storeMultiple(Request $request)
    {
        $validated = $request->validate([
            'cuotas_ids' => 'required|array',
            'cuotas_ids.*' => 'exists:cuotas,id',
            'medio_pago' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $totalCobrado = 0;
            $ultimoPdfUrl = null; 

            foreach ($validated['cuotas_ids'] as $id) {
                $cuota = Cuota::lockForUpdate()->find($id);

                if ($cuota->estado === 'PAGADA') {
                    continue;
                }

                // crear el Pago
                $montoReal = $cuota->saldo_pendiente ?? $cuota->importe ?? 0;

                $pago = Pago::create([
                    'cuota_id' => $cuota->id,
                    'monto_pagado' => $montoReal,
                    'fecha_pago' => now(),
                    'forma_pago' => $validated['medio_pago'],
                ]);

                // actualizar la Cuota
                $cuota->update(['estado' => 'PAGADA']);

                $inquilinoNombre = $cuota->contrato->inquilino->nombre_completo ?? 'Inquilino';
                
                Caja::create([
                    'tipo' => 'INGRESO',
                    'concepto' => "Cobro Alquiler Cuota #{$cuota->numero_cuota} - {$inquilinoNombre}",
                    'monto' => $montoReal,
                    'fecha' => now(),
                    'usuario_id' => Auth::id() ?? 1,
                ]);

                // D. Generar PDF del Recibo
                try {
                    $data = [
                        'pago' => $pago,
                        'cuota' => $cuota,
                        'contrato' => $cuota->contrato,
                        'inquilino' => $inquilinoNombre,
                        'direccion' => $cuota->contrato->propiedad->direccion ?? 'Dirección no disp.'
                    ];

                    // Generamos el PDF (Asegúrate de haber creado la vista 'pdf.recibo_pago')
                    $pdf = Pdf::loadView('pdf.recibo_pago', $data);

                    // Definimos nombre único y ruta
                    $nombreArchivo = 'recibos/pago_' . $pago->id . '_' . time() . '.pdf';
                    
                    // Guardamos en el disco 'public'
                    Storage::disk('public')->put($nombreArchivo, $pdf->output());

                    // Actualizamos el pago con la ruta del archivo en BD
                    $pago->ruta_pdf = $nombreArchivo;
                    $pago->save();

                    // Guardamos la URL pública para devolverla al frontend
                    $ultimoPdfUrl = asset('storage/' . $nombreArchivo);

                } catch (\Exception $e) {
                    // Si falla el PDF, logueamos el error pero NO revertimos el cobro
                    Log::error("Error generando PDF para pago ID {$pago->id}: " . $e->getMessage());
                }

                $totalCobrado += $montoReal;
            }

            DB::commit();

            return response()->json([
                'message' => 'Cobro registrado exitosamente',
                'total_cobrado' => $totalCobrado,
                'url_pdf' => $ultimoPdfUrl 
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
