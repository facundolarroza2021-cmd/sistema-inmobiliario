<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Propiedad;
use Carbon\Carbon;

class DashboardService
{
    public function obtenerEstadisticas(): array
    {
        $hoy = Carbon::now();
        $en30dias = Carbon::now()->addDays(30);

        return [
            'total_propiedades' => Propiedad::count(),

            'contratos_activos' => Contrato::where('activo', true)->count(),

            'deuda_pendiente' => Cuota::where('estado', '!=', 'PAGADO')->sum('saldo_pendiente'),

            'recaudado_mes' => Pago::whereYear('fecha_pago', $hoy->year)
                ->whereMonth('fecha_pago', $hoy->month)
                ->sum('monto_pagado'),

            'proximos_vencimientos' => Contrato::with(['inquilino', 'propiedad'])
                ->where('activo', true)
                ->whereBetween('fecha_fin', [$hoy, $en30dias])
                ->get(),

            'ultimos_pagos' => Pago::with('cuota.contrato.inquilino')
                ->orderBy('id', 'desc')
                ->take(5)
                ->get(),
        ];
    }
}
