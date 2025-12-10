<?php

namespace App\Http\Controllers;

use App\Models\Liquidacion;
use App\Models\Propietario;
use App\Models\Cuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Class LiquidacionController
 * * Gestiona el proceso de liquidación de ganancias a los propietarios.
 * Calcula ingresos, descuenta comisiones y gastos de mantenimiento.
 */

class LiquidacionController extends Controller
{
    /**
     * Genera y guarda una nueva liquidación.
     * * Flujo del proceso:
     * 1. Valida propietario y periodo.
     * 2. Calcula ingresos brutos (alquileres cobrados).
     * 3. Calcula la comisión de la inmobiliaria.
     * 4. Busca y suma gastos pendientes (que no han sido liquidados aún).
     * 5. Calcula el neto a pagar (Ingresos - Comisión - Gastos).
     * 6. Marca los gastos como "liquidados" para evitar duplicidad futura.
     *
     * @param Request $request
     * - propietario_id (int): ID del dueño.
     * - periodo (string): Formato 'YYYY-MM'.
     * * @return \Illuminate\Http\JsonResponse Estructura JSON con el resumen de la operación.
     */
    public function store(Request $request)
    {
        // 1. Validar datos
        $request->validate([
            'propietario_id' => 'required',
            'periodo' => 'required' 
        ]);

        $propietario = Propietario::findOrFail($request->propietario_id);
        
        // 2. Calcular Ingresos (Alquileres cobrados en ese periodo)
        $totalAlquileres = 100000; 
        
        // 3. Calcular Comision
        $comision = $totalAlquileres * 0.10;

        // 4. Obtener gastos no liquidados de todas las propiedades del dueño
        $gastos = Gasto::whereIn('propiedad_id', $propietario->propiedades->pluck('id'))
                        ->whereNull('liquidacion_id') 
                        ->get();

        $totalGastos = $gastos->sum('monto');

        // 5. El Total neto
        $aPagar = $totalAlquileres - $comision - $totalGastos;

        // 6. Guardar Liquidacion
        $liquidacion = Liquidacion::create([
            'propietario_id' => $propietario->id,
            'periodo' => $request->periodo,
            'monto_total' => $totalAlquileres,
            'monto_comision' => $comision,
            'monto_gastos' => $totalGastos, 
            'monto_entregado' => $aPagar
        ]);

        // 7. Marcar los gastos como "Liquidados"
        foreach($gastos as $gasto) {
            $gasto->update(['liquidacion_id' => $liquidacion->id]);
        }

        return response()->json(['message' => 'Liquidación creada', 'resumen' => $liquidacion]);
    }
    /**
     * Lista el historial de liquidaciones.
     *
     * @return \Illuminate\Database\Eloquent\Collection Lista ordenada descendentemente.
     */
    public function index()
    {
        return Liquidacion::with('propietario')
                    ->orderBy('id', 'desc')
                    ->get();
    }
}