<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Cuota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Servicio de Indexación: Gestiona la aplicación de ajustes inflacionarios o porcentuales
 * a las cuotas futuras de un contrato.
 */
class IndexacionService
{
    /**
     * Aplica un aumento porcentual al monto de alquiler de un contrato y actualiza las cuotas futuras.
     *
     * @param int $contratoId ID del contrato a ajustar.
     * @param float $porcentajeAumento El porcentaje de aumento a aplicar (ej: 0.25 para 25%).
     * @param Carbon $fechaAplicacion Fecha a partir de la cual se aplica el nuevo monto.
     * @param string $motivo Razón del ajuste (ej: 'INDEC', 'Acuerdo Propietario').
     * @return Contrato El contrato actualizado.
     * @throws Exception Si el contrato no existe o el porcentaje no es válido.
     */
    public function aplicarAjuste(int $contratoId, float $porcentajeAumento, Carbon $fechaAplicacion, string $motivo): Contrato
    {
        if ($porcentajeAumento <= 0) {
            throw new Exception("El porcentaje de aumento debe ser positivo.");
        }

        return DB::transaction(function () use ($contratoId, $porcentajeAumento, $fechaAplicacion, $motivo) {
            
            $contrato = Contrato::lockForUpdate()->findOrFail($contratoId);

            // 1. Calcular el nuevo monto de alquiler
            $montoActual = $contrato->monto_alquiler;
            $nuevoMonto = $montoActual * (1 + $porcentajeAumento);
            
            // 2. Actualizar el monto base del contrato
            $contrato->monto_alquiler = $nuevoMonto;
            // Se puede agregar un campo 'ultima_indexacion_fecha' y 'ultima_indexacion_motivo' al modelo Contrato.
            $contrato->save();

            // 3. Identificar y actualizar cuotas futuras (Indexación de Cuotas)
            // Se actualizan todas las cuotas que aún no han sido PAGADAS y cuyo período es
            // igual o posterior a la fecha de aplicación.
            $cuotasAfectadas = Cuota::where('contrato_id', $contratoId)
                ->where('periodo', '>=', $fechaAplicacion->format('Y-m'))
                ->whereNotIn('estado', ['PAGADO']) // No actualizar cuotas ya pagadas
                ->get();

            $cuotasAfectadas->each(function (Cuota $cuota) use ($montoActual, $nuevoMonto, $motivo) {
                // El aumento solo se aplica al IMPORTE BASE de la cuota.
                $diferencia = $nuevoMonto - $montoActual; 
                
                $cuota->importe = $nuevoMonto; // Nuevo canon base
                $cuota->saldo_pendiente += $diferencia; 
                
                $cuota->notas = "Ajuste aplicado: {$motivo} ({$diferencia})";
                
                $cuota->save();
            });

            return $contrato;
        });
    }

    /**
     * Lista contratos activos que son aptos para indexación (ej. aquellos que han cumplido 6 o 12 meses).
     * Nota: Por simplicidad, lista solo activos. La lógica de aptitud por fecha es del Frontend/Reporting.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listarContratosActivosParaAjuste()
    {
        return Contrato::with('inquilino', 'propiedad')
            ->where('estado', \App\Enums\ContratoEstado::ACTIVO)
            ->get();
    }
}