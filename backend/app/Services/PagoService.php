<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Cuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class PagoService
{
    /**
     * Registra el pago de una cuota, actualiza saldos y genera el recibo PDF.
     */
    public function registrarPago(int $cuotaId, float $monto, string $formaPago): array
    {
        return DB::transaction(function () use ($cuotaId, $monto, $formaPago) {
            
            $cuota = Cuota::lockForUpdate()->findOrFail($cuotaId);

            if ($cuota->estado === 'PAGADO') {
                throw new Exception("Esta cuota ya está pagada.");
            }

            if ($monto > $cuota->saldo_pendiente) {
                throw new Exception("El monto excede la deuda pendiente ($" . $cuota->saldo_pendiente . ")");
            }

            $pago = Pago::create([
                'cuota_id' => $cuota->id,
                'monto_pagado' => $monto,
                'fecha_pago' => now(),
                'metodo_pago' => $formaPago,
                'codigo_comprobante' => uniqid('REC-')
            ]);

            $nuevoSaldo = $cuota->saldo_pendiente - $monto;
            
            $cuota->saldo_pendiente = $nuevoSaldo;
            $cuota->estado = ($nuevoSaldo <= 0) ? 'PAGADO' : 'PARCIAL';
            $cuota->save();

            $urlPdf = $this->generarReciboPdf($pago, $cuota);

            return [
                'pago' => $pago,
                'nuevo_saldo' => $nuevoSaldo,
                'estado_cuota' => $cuota->estado,
                'url_pdf' => $urlPdf
            ];
        });
    }

    private function generarReciboPdf(Pago $pago, Cuota $cuota): string
    {
        $contrato = $cuota->contrato->load(['inquilino', 'propiedad']);

        $pdf = Pdf::loadView('pdf.recibo', [
            'pago' => $pago,
            'cuota' => $cuota,
            'contrato' => $contrato,
            'inquilino' => $contrato->inquilino
        ]);

        $nombreArchivo = 'recibos/recibo_' . $pago->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($nombreArchivo, $pdf->output());

        return asset('storage/' . $nombreArchivo);
    }
}