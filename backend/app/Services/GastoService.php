<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Cuota;
use App\Models\Gasto;
use Carbon\Carbon;
use Exception;

class GastoService
{
    public function listarPorPropiedad(int $propiedadId)
    {
        return Gasto::where('propiedad_id', $propiedadId)
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function crearGasto(array $datos): Gasto
    {
        $gasto = Gasto::create($datos);

        if ($datos['responsable'] === 'INQUILINO') {
            $this->impactarEnCuota($gasto);
        }

        return $gasto;
    }

    public function eliminarGasto(int $id): void
    {
        $gasto = Gasto::findOrFail($id);

        if ($gasto->liquidacion_id) {
            throw new Exception('No se puede borrar un gasto ya liquidado al propietario.');
        }

        $gasto->delete();
    }

    /**
     * Busca la cuota correspondiente y le suma la deuda.
     */
    private function impactarEnCuota(Gasto $gasto): void
    {
        $contrato = Contrato::where('propiedad_id', $gasto->propiedad_id)
            ->where('activo', true)
            ->first();

        if (! $contrato) {
            return;
        }

        $periodo = Carbon::parse($gasto->fecha)->format('Y-m');

        $cuota = Cuota::where('contrato_id', $contrato->id)
            ->where('periodo', $periodo)
            ->first();

        if ($cuota) {
            $cuota->monto_gastos += $gasto->monto;
            $cuota->saldo_pendiente += $gasto->monto;

            if ($cuota->saldo_pendiente > 0) {
                $cuota->estado = 'PENDIENTE';
            }

            $cuota->save();
        }
    }
}
