<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Cuota;
use App\Models\Gasto;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * Servicio de Gasto: Maneja la creación, eliminación y el impacto contable de los gastos
 * en las cuotas de inquilinos y en las liquidaciones a propietarios.
 */
class GastoService
{
    /**
     * Lista todos los gastos de una propiedad, ordenados por fecha.
     * @param int $propiedadId
     * @return Collection<Gasto>
     */
    public function listarPorPropiedad(int $propiedadId): Collection
    {
        return Gasto::where('propiedad_id', $propiedadId)
            ->orderBy('fecha', 'desc')
            ->get();
    }


    /**
     * Elimina un gasto existente.
     * @param int $id ID del gasto a eliminar.
     * @throws Exception Si el gasto ya fue liquidado al propietario.
     */
    public function eliminarGasto(int $id): void
    {
        $gasto = Gasto::findOrFail($id);

        if ($gasto->liquidacion_id) {
            throw new Exception('No se puede borrar un gasto ya liquidado al propietario.');
        }

        $gasto->delete();
    }

    // app/Services/GastoService.php (Versión mejorada y documentada)

// ... (inicio de la clase) ...

    /**
     * Crea un nuevo gasto y, si el responsable es el INQUILINO, impacta su cuota.
     * @param array $datos Datos validados del gasto.
     * @return Gasto
     */
    public function crearGasto(array $datos): Gasto
    {
        // 1. Crear el gasto
        $gasto = Gasto::create($datos);

        // 2. Si es responsabilidad del inquilino, actualizar la cuota
        if ($datos['responsable'] === 'INQUILINO') {
            $this->impactarEnCuota($gasto);
        }

        return $gasto;
    }

    /**
     * Busca la cuota correspondiente y le suma la deuda.
     * @param Gasto $gasto El modelo Gasto recién creado.
     */
    private function impactarEnCuota(Gasto $gasto): void
    {
        // Bloqueamos la lógica si no hay un contrato activo.
        $contrato = Contrato::where('propiedad_id', $gasto->propiedad_id)
            ->where('activo', true)
            ->first();

        if (! $contrato) {
            return;
        }

        // Determina el período de la cuota que debe ser afectada (basado en la fecha del gasto).
        $periodo = Carbon::parse($gasto->fecha)->format('Y-m');

        // Busca la cuota del contrato y período
        $cuota = Cuota::where('contrato_id', $contrato->id)
            ->where('periodo', $periodo)
            ->first();

        // FIX CRUCIAL: Solo impactar si la cuota ya fue generada por el sistema.
        if ($cuota) {
            // Usamos una transacción interna para el impacto, aunque no es estrictamente
            // necesario ya que la cuota solo se está actualizando.

            $cuota->monto_gastos += $gasto->monto;
            $cuota->saldo_pendiente += $gasto->monto;

            // Si el saldo pendiente ahora es positivo, aseguramos el estado "PENDIENTE".
            if ($cuota->saldo_pendiente > 0 && $cuota->estado === 'PAGADO') { 
                // Evitamos esto si la cuota ya estaba PAGADA antes del gasto. 
                // Solo cambiamos a PENDIENTE si era PARCIAL o si era 0.
                $cuota->estado = 'PARCIAL'; // Si era PAGADO, ahora es PARCIAL.
            } elseif ($cuota->saldo_pendiente > 0) {
                $cuota->estado = 'PENDIENTE';
            }

            $cuota->save();
            
            // Opcional: Marcar el gasto para rastreo.
            // $gasto->cuota_id = $cuota->id;
            // $gasto->save();
        }
    }
// ... (resto de la clase) ...
    /**
     * [NUEVA FUNCIÓN REQUERIDA] Recupera todos los gastos que son responsabilidad
     * del PROPIETARIO y que aún no han sido incluidos en una Liquidación.
     * * @param array $propiedadIds Array de IDs de las propiedades del propietario.
     * @return Collection<Gasto>
     */
    public function obtenerGastosPendientesParaLiquidacion(array $propiedadIds): Collection
    {
        return Gasto::whereIn('propiedad_id', $propiedadIds)
                    ->where('responsable', 'PROPIETARIO')
                    ->whereNull('liquidacion_id')
                    ->get();
    }
}