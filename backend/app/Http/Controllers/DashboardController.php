<?php

namespace App\Http\Controllers;

use App\Models\Propiedad;
use App\Models\Contrato;
use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculamos fechas para las alertas
        $hoy = Carbon::now();
        $en30dias = Carbon::now()->addDays(30);

        return response()->json([
            // TARJETAS SUPERIORES (KPIs)
            'total_propiedades' => Propiedad::count(),
            'contratos_activos' => Contrato::where('activo', true)->count(),
            'deuda_pendiente' => Cuota::where('estado', '!=', 'PAGADO')->sum('saldo_pendiente'),
            
            // Recaudación del mes actual
            'recaudado_mes' => Pago::whereYear('fecha_pago', $hoy->year)
                                    ->whereMonth('fecha_pago', $hoy->month)
                                    ->sum('monto_pagado'),

            // NUEVO: ALERTAS (Contratos que vencen pronto)
            'proximos_vencimientos' => Contrato::with(['inquilino', 'propiedad'])
                                        ->where('activo', true)
                                        ->whereBetween('fecha_fin', [$hoy, $en30dias])
                                        ->get(),
                                        
            // NUEVO: ÚLTIMOS COBROS (Para ver actividad reciente)
            'ultimos_pagos' => Pago::with('cuota.contrato.inquilino')
                                    ->orderBy('id', 'desc')
                                    ->take(5) // Solo los últimos 5
                                    ->get()
        ]);
    }
}