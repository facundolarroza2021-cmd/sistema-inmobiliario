<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Cuota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Indexación: Gestiona la aplicación de ajustes inflacionarios o porcentuales
 * a las cuotas futuras de uno o varios contratos.
 */
class IndexacionService
{
    /**
     * Previsualiza los contratos y cuotas que se verán afectados por el ajuste.
     * * @param array $datos ['tipoAjuste', 'valorAjuste', 'fechaAplicacion']
     * @return Collection
     */
    public function previsualizarAjuste(array $datos): Collection
    {
        $fechaAplicacion = Carbon::parse($datos['fechaAplicacion'])->format('Y-m');
        $valorAjuste = $datos['valorAjuste'];
        $tipoAjuste = $datos['tipoAjuste'];

        // 1. Obtener contratos activos con información necesaria para la vista previa
        $contratos = Contrato::with('inquilino', 'propiedad') // Incluimos Propiedad e Inquilino
            ->where('estado', \App\Enums\ContratoEstado::ACTIVO)
            ->get();
        
        $contratosAjustables = collect();

        foreach ($contratos as $contrato) {
            // 2. Contar cuotas futuras pendientes afectadas
            // Excluímos 'PAGADO' y 'LIQUIDADO' para evitar modificar cuotas cerradas
            $cuotasAfectadasCount = Cuota::where('contrato_id', $contrato->id)
                ->where('periodo', '>=', $fechaAplicacion)
                ->whereNotIn('estado', ['PAGADO', 'LIQUIDADO']) 
                ->count();
            
            // Solo agregar si hay cuotas que ajustar
            if ($cuotasAfectadasCount > 0) {
                $montoActual = $contrato->monto_alquiler;
                $nuevoMonto = $this->calcularNuevoMonto($montoActual, $tipoAjuste, $valorAjuste);

                $contratosAjustables->push([
                    'id' => $contrato->id,
                    'inquilino' => $contrato->inquilino,
                    'propiedad' => $contrato->propiedad,
                    'monto_alquiler' => $montoActual,
                    'nuevo_monto_alquiler' => $nuevoMonto,
                    'cuotas_afectadas' => $cuotasAfectadasCount,
                ]);
            }
        }

        return $contratosAjustables;
    }

    /**
     * Aplica un ajuste de monto a un conjunto de contratos y actualiza sus cuotas futuras.
     * * @param array $contratoIds IDs de los contratos a ajustar.
     * @param string $tipoAjuste 'porcentaje' o 'monto_fijo'.
     * @param float $valorAjuste El valor del ajuste (ej: 10 para 10% o 1000 para $1000).
     * @param string $fechaAplicacion Fecha (YYYY-MM-DD) a partir de la cual se aplica el nuevo monto.
     * @return int El número de contratos ajustados exitosamente.
     * @throws Exception Si el valor de ajuste no es válido.
     */
    public function aplicarAjusteMasivo(array $contratoIds, string $tipoAjuste, float $valorAjuste, string $fechaAplicacion): int
    {
        if ($valorAjuste <= 0) {
            throw new Exception("El valor de ajuste debe ser positivo.");
        }

        $fechaAplicacionCarbon = Carbon::parse($fechaAplicacion);
        $fechaAplicacionPeriodo = $fechaAplicacionCarbon->format('Y-m');
        $contratosAjustadosCount = 0;

        return DB::transaction(function () use ($contratoIds, $tipoAjuste, $valorAjuste, $fechaAplicacionPeriodo, &$contratosAjustadosCount) {
            
            $contratos = Contrato::whereIn('id', $contratoIds)
                ->where('estado', \App\Enums\ContratoEstado::ACTIVO)
                ->lockForUpdate()
                ->get();

            foreach ($contratos as $contrato) {
                $montoActual = $contrato->monto_alquiler;
                $nuevoMonto = $this->calcularNuevoMonto($montoActual, $tipoAjuste, $valorAjuste);
                $diferencia = $nuevoMonto - $montoActual;
                
                if ($diferencia <= 0.009) {
                    continue; 
                }

                // 1. Actualizar el monto base del contrato
                $contrato->monto_alquiler = $nuevoMonto;
                $contrato->save();

                // 2. Identificar y actualizar cuotas futuras
                $cuotasAfectadas = Cuota::where('contrato_id', $contrato->id)
                    ->where('periodo', '>=', $fechaAplicacionPeriodo)
                    ->whereNotIn('estado', ['PAGADO', 'LIQUIDADO']) 
                    ->get();

                $motivo = "Ajuste Masivo de {$tipoAjuste} ({$valorAjuste}). Nuevo monto base: {$nuevoMonto}";

                $cuotasAfectadas->each(function (Cuota $cuota) use ($nuevoMonto, $diferencia, $motivo) {
                    // *** CORRECCIÓN: Usar monto_original en lugar de importe ***
                    $cuota->monto_original = $nuevoMonto; 
                    $cuota->saldo_pendiente = $cuota->saldo_pendiente + $diferencia; 
                    
                    // Asumiendo que 'notas' existe en el modelo Cuota
                    // Nota: Si 'notas' no existe, esto podría causar otro error
                    //if (property_exists($cuota, 'notas')) {
                      //  $cuota->notas .= " | {$motivo}";
                    //}
                    
                    $cuota->save();
                });
                $contratosAjustadosCount++;
            }
            Log::info("DEBUG INDEXACION: Finalizando transacción. Total ajustados: {$contratosAjustadosCount}");
            return $contratosAjustadosCount;
        });
    }

    /**
     * Función utilitaria para calcular el nuevo monto base.
     */
    protected function calcularNuevoMonto(float $montoActual, string $tipoAjuste, float $valorAjuste): float
    {
        if ($tipoAjuste === 'porcentaje') {
            return round($montoActual * (1 + $valorAjuste / 100), 2);
        } elseif ($tipoAjuste === 'monto_fijo') {
            return round($montoActual + $valorAjuste, 2);
        }
        return $montoActual;
    }
    
    // El método listarContratosActivosParaAjuste() se mantiene igual
    public function listarContratosActivosParaAjuste()
    {
        return Contrato::with('inquilino', 'propiedad')
            ->where('estado', \App\Enums\ContratoEstado::ACTIVO)
            ->get();
    }
}