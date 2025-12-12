<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Cuota;
use Carbon\Carbon;

class CuotaService
{
    /**
     * Genera la proyección de cuotas para un contrato nuevo.
     */
    public function generarCuotasParaContrato(Contrato $contrato, int $meses, int $diaVencimiento): void
    {
        $fechaAux = Carbon::parse($contrato->fecha_inicio);
        $fechaFin = Carbon::parse($contrato->fecha_fin);
        $numeroCuota = 1;

        while ($fechaAux->lt($fechaFin)) {
            $vencimiento = $fechaAux->copy()->day($diaVencimiento);

            Cuota::create([
                'contrato_id' => $contrato->id,
                'numero_cuota' => $numeroCuota,
                'periodo' => $fechaAux->format('Y-m'),
                'fecha_vencimiento' => $vencimiento->format('Y-m-d'),
                'monto_original' => $contrato->monto_alquiler,
                'saldo_pendiente' => $contrato->monto_alquiler,
                'estado' => 'PENDIENTE',
            ]);

            $fechaAux->addMonth();
            $numeroCuota++;
        }
    }

    /**
     * Listado avanzado de cuotas con relaciones.
     */
    public function listarCuotas()
    {
        return Cuota::with(['contrato.inquilino', 'contrato.propiedad', 'pagos'])
            ->orderBy('id', 'desc')
            ->get();
    }
}
