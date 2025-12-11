<?php

namespace App\Services;

use App\Models\MovimientoCaja;
use Illuminate\Support\Carbon;

class CajaService
{
    /**
     * Obtiene los movimientos filtrados por mes y año.
     */
    public function listarMovimientos(?int $mes, ?int $anio)
    {
        $mes = $mes ?? Carbon::now()->month;
        $anio = $anio ?? Carbon::now()->year;

        return MovimientoCaja::with('usuario')
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha', 'desc')
            ->get();
    }

    /**
     * Registra un nuevo movimiento.
     */
    public function registrarMovimiento(array $datos, int $userId): MovimientoCaja
    {
        return MovimientoCaja::create([
            'fecha' => $datos['fecha'],
            'tipo' => $datos['tipo'],
            'categoria' => $datos['categoria'],
            'monto' => $datos['monto'],
            'descripcion' => $datos['descripcion'] ?? null,
            'user_id' => $userId
        ]);
    }

    /**
     * Calcula los totales del mes.
     */
    public function calcularBalance(?int $mes, ?int $anio): array
    {
        $mes = $mes ?? Carbon::now()->month;
        $anio = $anio ?? Carbon::now()->year;

        // Sumar INGRESOS
        $ingresos = MovimientoCaja::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->where('tipo', 'INGRESO')
            ->sum('monto');

        // Sumar EGRESOS
        $egresos = MovimientoCaja::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->where('tipo', 'EGRESO')
            ->sum('monto');

        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'balance_neto' => $ingresos - $egresos
        ];
    }

    public function eliminarMovimiento(int $id): void
    {
        $movimiento = MovimientoCaja::findOrFail($id);
        $movimiento->delete();
    }
}