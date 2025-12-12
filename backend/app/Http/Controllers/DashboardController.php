<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Propiedad;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::now();
        $en30dias = Carbon::now()->addDays(30);

        return response()->json([
            'total_propiedades' => Propiedad::count(),
            'contratos_activos' => Contrato::where('activo', true)->count(),
            'deuda_pendiente' => Cuota::where('estado', '!=', 'PAGADO')->sum('saldo_pendiente'),

            // Recaudación del mes actual
            'recaudado_mes' => Pago::whereYear('fecha_pago', $hoy->year)
                ->whereMonth('fecha_pago', $hoy->month)
                ->sum('monto_pagado'),

            // ALERTAS (Contratos que vencen pronto)
            'proximos_vencimientos' => Contrato::with(['inquilino', 'propiedad'])
                ->where('activo', true)
                ->whereBetween('fecha_fin', [$hoy, $en30dias])
                ->get(),

            // ULTIMOS COBROS (Para ver actividad reciente)
            'ultimos_pagos' => Pago::with('cuota.contrato.inquilino')
                ->orderBy('id', 'desc')
                ->take(5)
                ->get(),
        ]);
    }
}
