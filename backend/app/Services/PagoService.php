<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PagoService
{
    /**
     * Registra el pago de una sola cuota bajo una transacción atómica.
     *
     * **Flujo:** * 1. Bloquea la cuota para lectura/escritura (lockForUpdate).
     * 2. Valida las reglas de negocio (estado, monto).
     * 3. Crea el registro Pago (forma_pago, nro_comprobante).
     * 4. Actualiza el saldo pendiente y el estado de la Cuota (PARCIAL/PAGADO).
     * 5. Genera y guarda el recibo PDF, actualizando la ruta_pdf en el modelo Pago.
     *
     * @param int $cuotaId El ID de la cuota a pagar.
     * @param float $monto El monto exacto aplicado.
     * @param string $formaPago El método de pago utilizado.
     * @param ?string $observacion Notas adicionales.
     * @return array Contiene el objeto pago, nuevo saldo, estado y url_pdf.
     * @throws Exception Si la cuota ya está PAGADO o el monto excede el saldo pendiente.
     */
    public function registrarPago(int $cuotaId, float $monto, string $formaPago, ?string $observacion = null): array
    {

        return DB::transaction(function () use ($cuotaId, $monto, $formaPago, $observacion) {

            $cuota = Cuota::lockForUpdate()->findOrFail($cuotaId);

            if ($cuota->estado === 'PAGADO') {
                throw new Exception('Esta cuota ya está pagada.');
            }

            if ($monto > $cuota->saldo_pendiente) {
                throw new Exception('El monto excede la deuda pendiente ($'.$cuota->saldo_pendiente.')');
            }

            $pago = Pago::create([
                'cuota_id' => $cuota->id,
                'monto_pagado' => $monto,
                'fecha_pago' => now(),
                'forma_pago' => $formaPago, 
                'nro_comprobante' => uniqid('REC-'),
                'observacion' => $observacion, 
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
                'url_pdf' => $urlPdf,
            ];
        });
    }

    /**
     * Procesa un cobro total que se aplica de forma decremental a múltiples cuotas.
     *
     * **Flujo:**
     * 1. Inicia la transacción y define el monto restante.
     * 2. Itera sobre los IDs de cuotas, creando un registro Pago por cada cuota afectada.
     * 3. Aplica el monto decrementalmente, asegurando que el monto total pagado no se exceda.
     * 4. Actualiza saldos y estados de cuotas (PARCIAL/PAGADO).
     * 5. Genera un único recibo PDF consolidado con el detalle de todos los pagos aplicados.
     *
     * @param int[] $cuotasIds Array de IDs de las cuotas seleccionadas.
     * @param float $montoTotal El monto total exacto recibido del cliente.
     * @param string $medioPago El método de pago utilizado (forma_pago).
     * @param ?string $observacion Notas adicionales.
     * @return array Retorna el resumen del cobro, monto aplicado y la url_pdf del recibo consolidado.
     * @throws Exception Si el monto no se pudo aplicar a ninguna cuota (ej. cuotas pagadas).
     */

    public function registrarPagoMultiple(array $cuotasIds, float $montoTotal, string $medioPago, ?string $observacion = null): array
    {
        // FIX: Incluimos todas las variables necesarias en la lista 'use'
        return DB::transaction(function () use ($cuotasIds, $montoTotal, $medioPago, $observacion) {

            $montoRestante = $montoTotal;
            $pagosCreados = [];
            
            // 1. Procesar cada cuota seleccionada
            foreach ($cuotasIds as $cuotaId) {
                $cuota = Cuota::lockForUpdate()->findOrFail($cuotaId);

                if ($cuota->estado === 'PAGADO') {
                    continue; 
                }

                $montoAPagar = min($montoRestante, $cuota->saldo_pendiente);

                if ($montoAPagar <= 0) {
                    continue; 
                }

                // 2. Crear el pago para esta cuota específica
                $pago = Pago::create([
                    'cuota_id' => $cuota->id,
                    'monto_pagado' => $montoAPagar,
                    'fecha_pago' => now(),
                    'forma_pago' => $medioPago, // USAMOS forma_pago
                    'nro_comprobante' => uniqid('REC-MUL-'), // USAMOS nro_comprobante
                    'observacion' => $observacion,
                ]);
                
                $pagosCreados[] = $pago;

                // 3. Actualizar el saldo de la cuota
                $nuevoSaldo = $cuota->saldo_pendiente - $montoAPagar;
                $cuota->saldo_pendiente = $nuevoSaldo;
                $cuota->estado = ($nuevoSaldo <= 0) ? 'PAGADO' : 'PARCIAL';
                $cuota->save();

                // 4. Reducir el monto total restante
                $montoRestante -= $montoAPagar;

                if ($montoRestante <= 0) {
                    break; 
                }
            }

            if (empty($pagosCreados)) {
                throw new Exception('No se pudo aplicar el pago a ninguna cuota. Verifique los saldos.');
            }
            
            // 5. Generar un único recibo para el pago múltiple
            $urlPdfRecibo = $this->generarReciboMultiplePdf($pagosCreados);

            return [
                'pagos_registrados' => count($pagosCreados),
                'monto_aplicado' => $montoTotal - $montoRestante,
                'url_pdf' => $urlPdfRecibo,
            ];
        });
    }
    
    /**
     * Genera el recibo PDF para un pago único.
     *
     * @param Pago $pago Modelo Pago recién creado y con ruta_pdf actualizada.
     * @param Cuota $cuota Modelo Cuota asociado.
     * @return string La URL pública del recibo PDF.
     */

    private function generarReciboPdf(Pago $pago, Cuota $cuota): string
        {
            $contrato = $cuota->contrato;
            $contrato->load(['inquilino', 'propiedad']);

            // Asumo que la vista para pago único es 'pdf.recibo'
            $pdf = Pdf::loadView('pdf.recibo', [
                'pago' => $pago,
                'cuota' => $cuota,
                'contrato' => $contrato,
                'inquilino' => $contrato->inquilino 
            ]);

            $nombreArchivo = 'recibos/recibo_' . $pago->id . '_' . time() . '.pdf';
            Storage::disk('public')->put($nombreArchivo, $pdf->output());

            $pago->ruta_pdf = $nombreArchivo;
            $pago->save();

            return asset('storage/' . $nombreArchivo);
        }

    /**
     * Genera un único recibo PDF consolidado que incluye el detalle de múltiples pagos.
     *
     * @param Pago[] $pagosCreados Array de modelos Pago generados en la transacción.
     * @return string La URL pública del recibo PDF consolidado.
     */
    private function generarReciboMultiplePdf(array $pagosCreados): string
    {
        // 1. Obtener información de la primera transacción para datos del recibo principal
        $primerPago = $pagosCreados[0];
        // Aseguramos que las relaciones de la primera cuota estén cargadas
        $primerPago->load(['cuota.contrato.inquilino', 'cuota.contrato.propiedad']); 
        $contrato = $primerPago->cuota->contrato;

        $montoTotal = collect($pagosCreados)->sum('monto_pagado');
        $codigoComprobante = 'MUL-' . time();
        
        // FIX: Eliminamos ->toArray() para mantener los modelos como objetos
        $cuotasPagadas = collect($pagosCreados)->map(fn($p) => $p->cuota)->values(); 
        
        // Cargar la vista
        $pdf = Pdf::loadView('pdf.recibo_multiple', [
            'pagos' => $pagosCreados,        
            'cuotas' => $cuotasPagadas,       // Ahora son objetos (Collection de Cuota Models)
            'monto_total' => $montoTotal,
            'codigo_comprobante' => $codigoComprobante,
            'pago_principal' => $primerPago, 
            'contrato' => $contrato,
            'inquilino' => $contrato->inquilino,
            'localidad' => $contrato->propiedad->localidad ?? 'No especificada',
        ]);

        $nombreArchivo = 'recibos_multiples/recibo_mul_' . $codigoComprobante . '.pdf';
        Storage::disk('public')->put($nombreArchivo, $pdf->output());

        foreach ($pagosCreados as $pago) {
            $pago->ruta_pdf = $nombreArchivo;
            $pago->save();
        }

        return asset('storage/' . $nombreArchivo);
    }
}