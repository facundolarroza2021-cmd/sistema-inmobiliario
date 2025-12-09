<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf; // Asegúrate de importar esto

class PagoController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validamos
        $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto' => 'required|numeric|min:1',
            'forma_pago' => 'required|string' // Efectivo, Transferencia, etc.
        ]);

        // Usamos una transacción por seguridad
        try {
            return DB::transaction(function () use ($request) {
                
                $cuota = Cuota::lockForUpdate()->find($request->cuota_id);

                if ($request->monto > $cuota->saldo_pendiente) {
                    return response()->json(['error' => 'El monto excede el saldo pendiente'], 400);
                }

                // B. Creamos el registro del Pago
                $pago = Pago::create([
                    'cuota_id' => $cuota->id,
                    'fecha_pago' => now(),
                    'monto_pagado' => $request->monto,
                    'forma_pago' => $request->forma_pago,
                    'nro_comprobante' => 'REC-' . time(), // Generamos un ID único temporal
                ]);

                // C. Actualizamos la Cuota (Restamos saldo)
                $cuota->saldo_pendiente -= $request->monto;

                // Definimos estado
                if ($cuota->saldo_pendiente <= 0) {
                    $cuota->saldo_pendiente = 0; // Por las dudas
                    $cuota->estado = 'PAGADO';
                } else {
                    $cuota->estado = 'PARCIAL';
                }
                $cuota->save();

                // Cargamos la vista 'pdf.recibo' y le pasamos los datos
                $pdf = Pdf::loadView('pdf.recibo', compact('pago', 'cuota'));
                
                // Definimos nombre y guardamos en disco
                $nombreArchivo = 'recibos/recibo_' . $pago->id . '.pdf';
                Storage::disk('public')->put($nombreArchivo, $pdf->output());

                // Guardamos la ruta en el pago
                $pago->ruta_pdf = $nombreArchivo;
                $pago->save();

                // E. Respuesta para Angular
                return response()->json([
                    'mensaje' => 'Pago registrado con éxito',
                    'nuevo_saldo' => $cuota->saldo_pendiente,
                    'estado_cuota' => $cuota->estado,
                    'url_pdf' => asset('storage/' . $nombreArchivo) 
                ]);
            });

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar el pago: ' . $e->getMessage()], 500);
        }
    }

    public function storeMultiple(Request $request)
    {
        $request->validate([
            'cuota_ids' => 'required|array',
            'forma_pago' => 'required',
            'monto_recibido' => 'required|numeric|min:1',
            'observacion' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request) {
            
            // Traer cuotas ordenadas (cronológicamente)
            $cuotas = Cuota::whereIn('id', $request->cuota_ids)
                        ->orderBy('periodo', 'asc')
                        ->get();

            $pago = Pago::create([
                'cuota_id' => $cuotas->first()->id,
                'monto_pagado' => $request->monto_recibido,
                'fecha_pago' => now(),
                'forma_pago' => $request->forma_pago,
                'observacion' => $request->observacion,
                'codigo_comprobante' => 'REC-' . time()
            ]);

            // LÓGICA DE CASCADA DETALLADA
            $dineroDisponible = $request->monto_recibido;
            $cuotasParaRecibo = collect(); 

            foreach($cuotas as $cuota) {
                if ($dineroDisponible <= 0) break;

                // Guardamos el estado ANTES de pagar para mostrar en el recibo
                $deudaOriginal = $cuota->saldo_pendiente;
                $montoAbonadoHoy = 0;

                if ($dineroDisponible >= $deudaOriginal) {
                    // PAGO TOTAL DE LA CUOTA
                    $montoAbonadoHoy = $deudaOriginal;
                    
                    $cuota->update([
                        'saldo_pendiente' => 0,
                        'estado' => 'PAGADO'
                    ]);
                } else {
                    // PAGO PARCIAL
                    $montoAbonadoHoy = $dineroDisponible;
                    $nuevoSaldo = $deudaOriginal - $dineroDisponible;
                    
                    $cuota->update([
                        'saldo_pendiente' => $nuevoSaldo,
                        'estado' => 'PARCIAL'
                    ]);
                }

                $dineroDisponible -= $montoAbonadoHoy;

                // --- TRUCO MAGICO ---
                // Agregamos propiedades temporales al objeto cuota solo para el PDF
                $cuota->detalle_pago_hoy = $montoAbonadoHoy;
                $cuota->detalle_saldo_restante = $cuota->saldo_pendiente;
                $cuota->detalle_total_cuota = $cuota->monto_total;
                
                $cuotasParaRecibo->push($cuota);
            }

            // Generar PDF
            $contrato = $cuotas->first()->contrato;
            
            // Pasamos la colección enriquecida con los detalles
            $pdf = Pdf::loadView('pdf.recibo', [
                'pago' => $pago, 
                'cuotas' => $cuotasParaRecibo, 
                'contrato' => $contrato
            ]);
            
            $nombreArchivo = 'recibos/recibo_multiple_' . $pago->id . '.pdf';
            Storage::disk('public')->put($nombreArchivo, $pdf->output());

            $pago->ruta_pdf = $nombreArchivo;
            $pago->save();

            return response()->json([
                'mensaje' => 'Pago registrado correctamente',
                'url_pdf' => asset('storage/' . $nombreArchivo)
            ]);
        });
    }
}