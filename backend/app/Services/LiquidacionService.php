<?php

namespace App\Services;

use App\Models\Liquidacion;
use App\Models\Propietario;
use App\Models\Gasto;
use Exception;

class LiquidacionService
{
    public function generarLiquidacion(int $propietarioId, string $periodo): Liquidacion
    {
        $propietario = Propietario::with('propiedades')->findOrFail($propietarioId);

        $totalIngresos = 100000;

        $comision = $totalIngresos * 0.10;
        $idsPropiedades = $propietario->propiedades->pluck('id');
        
        $gastosPendientes = Gasto::whereIn('propiedad_id', $idsPropiedades)
                                 ->whereNull('liquidacion_id')
                                 ->get();

        $totalGastos = $gastosPendientes->sum('monto');

        $montoPagar = $totalIngresos - $comision - $totalGastos;

        if ($montoPagar < 0) {
            throw new Exception("El saldo es negativo. No se puede liquidar.");
        }

        $liquidacion = Liquidacion::create([
            'propietario_id' => $propietario->id,
            'periodo' => $periodo,
            'monto_total' => $totalIngresos,
            'monto_comision' => $comision,
            'monto_gastos' => $totalGastos,
            'monto_entregado' => $montoPagar
        ]);

        foreach ($gastosPendientes as $gasto) {
            $gasto->liquidacion_id = $liquidacion->id;
            $gasto->save();
        }

        return $liquidacion;
    }
    
    public function listarLiquidaciones()
    {
        return Liquidacion::with('propietario')->orderBy('id', 'desc')->get();
    }
}