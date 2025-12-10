<?php

namespace App\Http\Controllers;

use App\Models\MovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CajaController extends Controller
{
    // Listado de movimientos (Por defecto del mes actual)
    public function index(Request $request)
    {
        $mes = $request->query('mes', Carbon::now()->month);
        $anio = $request->query('anio', Carbon::now()->year);

        return MovimientoCaja::with('usuario')
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha', 'desc')
            ->get();
    }

    // Registrar un nuevo movimiento
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'tipo' => 'required|in:INGRESO,EGRESO',
            'categoria' => 'required|string',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string'
        ]);

        $movimiento = MovimientoCaja::create([
            'fecha' => $request->fecha,
            'tipo' => $request->tipo,
            'categoria' => $request->categoria,
            'monto' => $request->monto,
            'descripcion' => $request->descripcion,
            'user_id' => $request->user()->id // Guardamos quién lo hizo
        ]);

        return response()->json($movimiento, 201);
    }

    // Obtener el Balance (Ingresos vs Egresos)
    public function balance(Request $request)
    {
        $mes = $request->query('mes', Carbon::now()->month);
        $anio = $request->query('anio', Carbon::now()->year);

        // Sumar todos los INGRESOS del mes
        $ingresos = MovimientoCaja::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->where('tipo', 'INGRESO')
            ->sum('monto');

        // Sumar todos los EGRESOS del mes
        $egresos = MovimientoCaja::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->where('tipo', 'EGRESO')
            ->sum('monto');

        return response()->json([
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'balance_neto' => $ingresos - $egresos
        ]);
    }
    
    // Eliminar (Solo admin debería poder hacer esto)
    public function destroy($id)
    {
        $movimiento = MovimientoCaja::findOrFail($id);
        $movimiento->delete();
        return response()->json(['message' => 'Movimiento eliminado']);
    }
}