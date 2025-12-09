<?php

namespace App\Http\Controllers;

use App\Models\Liquidacion;
use App\Models\Propietario;
use App\Models\Cuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class LiquidacionController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validar datos
        $request->validate([
            'propietario_id' => 'required',
            'periodo' => 'required' // Ej: '2025-12'
        ]);

        $propietario = Propietario::findOrFail($request->propietario_id);
        
        // 2. Calcular Ingresos (Alquileres cobrados en ese periodo)
        // ... (Tu lógica actual de sumar alquileres) ...
        $totalAlquileres = 100000; // (Ejemplo simplificado)
        
        // 3. Calcular Comisión
        $comision = $totalAlquileres * 0.10;

        // 4. NUEVO: Buscar Gastos Pendientes de todas las propiedades de este dueño
        $gastos = Gasto::whereIn('propiedad_id', $propietario->propiedades->pluck('id'))
                        ->whereNull('liquidacion_id') // Solo los que no se cobraron
                        ->get();

        $totalGastos = $gastos->sum('monto');

        // 5. El Total
        $aPagar = $totalAlquileres - $comision - $totalGastos;

        // 6. Guardar Liquidación
        $liquidacion = Liquidacion::create([
            'propietario_id' => $propietario->id,
            'periodo' => $request->periodo,
            'monto_total' => $totalAlquileres,
            'monto_comision' => $comision,
            'monto_gastos' => $totalGastos, 
            'monto_entregado' => $aPagar
        ]);

        // 7. IMPORTANTE: Marcar los gastos como "Liquidados"
        // Así no se vuelven a descontar el mes que viene.
        foreach($gastos as $gasto) {
            $gasto->update(['liquidacion_id' => $liquidacion->id]);
        }

        return response()->json(['message' => 'Liquidación creada', 'resumen' => $liquidacion]);
    }
    public function index()
    {
        return Liquidacion::with('propietario')
                    ->orderBy('id', 'desc')
                    ->get();
    }
}