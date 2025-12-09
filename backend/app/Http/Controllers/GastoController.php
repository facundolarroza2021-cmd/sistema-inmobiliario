<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\Propiedad;
use App\Models\Contrato;
use App\Models\Cuota;
use Carbon\Carbon;

class GastoController extends Controller
{
    // Listar gastos de una propiedad (para la pestaña del detalle)
    public function byPropiedad($id)
    {
        return Gasto::where('propiedad_id', $id)
                    ->orderBy('fecha', 'desc')
                    ->get();
    }

    public function store(Request $request)
    {
        // 1. Validaciones (Las que ya tenías)
        $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'concepto' => 'required|string',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'responsable' => 'required|string'
        ]);

        // 2. Crear el Gasto
        $gasto = Gasto::create($request->all());

        // 3. --- AUTOMATIZACIÓN DE DEUDA (MAGIA) ---
        // Si el gasto lo paga el INQUILINO, impactamos en su Cuota.
        if ($request->responsable === 'INQUILINO') {
            
            // A. Buscar contrato activo de esta propiedad
            $contrato = Contrato::where('propiedad_id', $request->propiedad_id)
                                ->where('activo', true)
                                ->first();

            if ($contrato) {
                // B. Determinar a qué periodo corresponde el gasto (Año-Mes)
                // Ej: Si el gasto es del 15/12/2025, impacta en la cuota "2025-12"
                $periodo = Carbon::parse($request->fecha)->format('Y-m');

                // C. Buscar la cuota de ese mes
                $cuota = Cuota::where('contrato_id', $contrato->id)
                              ->where('periodo', $periodo)
                              ->first();

                if ($cuota) {
                    // D. Actualizar la cuota
                    $cuota->monto_gastos += $gasto->monto;       // Sumamos al acumulador de gastos
                    $cuota->saldo_pendiente += $gasto->monto;    // Aumentamos la deuda total
                    
                    // Si la cuota ya estaba PAGADA, la volvemos a abrir porque ahora debe plata nueva
                    if ($cuota->saldo_pendiente > 0) {
                         $cuota->estado = 'PENDIENTE'; // O 'PARCIAL' si quieres
                    }
                    
                    $cuota->save();
                }
            }
        }

        return response()->json($gasto);
    }

    // Borrar gasto (Solo si no está liquidado aún)
    public function destroy($id)
    {
        $gasto = Gasto::findOrFail($id);
        
        if ($gasto->liquidacion_id) {
            return response()->json(['error' => 'No se puede borrar un gasto ya liquidado'], 400);
        }

        $gasto->delete();
        return response()->json(['message' => 'Gasto eliminado']);
    }
}