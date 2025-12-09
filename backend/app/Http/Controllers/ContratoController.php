<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Cuota;
use App\Models\Garante;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ContratoController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validar
        $request->validate([
            'inquilino_id' => 'required',
            'propiedad_id' => 'required',
            'monto_actual' => 'required', // Quitamos 'numeric' estricto por si llega como string
            'fecha_inicio' => 'required|date', 
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'garantes' => 'nullable', 
        ]);
        $rutaArchivo = null;
        if ($request->hasFile('archivo')) {
            // Lo guardamos en la carpeta 'contratos' dentro del disco 'public'
            $rutaArchivo = $request->file('archivo')->store('contratos', 'public');
        }

        // 2. Calcular Fecha Fin Automáticamente
        $cantidadMeses = (int) ($request->meses ?? 12);
        $inicio = Carbon::parse($request->fecha_inicio);
        $fin = $inicio->copy()->addMonths($cantidadMeses);

        // 3. Crear Contrato
        $contrato = Contrato::create([
            'inquilino_id' => $request->inquilino_id,
            'propiedad_id' => $request->propiedad_id,
            'monto_alquiler' => $request->monto_actual,
            'fecha_inicio' => $inicio->format('Y-m-d'),
            'fecha_fin' => $fin->format('Y-m-d'),
            'dia_vencimiento' => (int) $request->dia_vencimiento,
            'activo' => true,
            'archivo_url' => $rutaArchivo
        ]);

        if ($request->has('garantes')) {
            // Decodificamos el TEXTO JSON a un ARRAY PHP real
            $garantesData = json_decode($request->garantes, true);
            
            if (is_array($garantesData)) {
                foreach ($garantesData as $g) {
                    $contrato->garantes()->create([
                        'nombre_completo' => $g['nombre_completo'],
                        'dni' => $g['dni'],
                        'telefono' => $g['telefono'] ?? null,
                        'tipo_garantia' => $g['tipo'],
                        'detalle_garantia' => $g['detalle'] ?? null
                    ]);
                }
            }
        }

        // 4. Generar Cuotas (Tu código que ya funcionaba)
        $fecha_aux = $inicio->copy();
        $numero_cuota = 1;

        while ($fecha_aux->lt($fin)) {
            $vencimiento = $fecha_aux->copy()->day((int) $request->dia_vencimiento);
            
            Cuota::create([
                'contrato_id' => $contrato->id,
                'numero_cuota' => $numero_cuota,
                'periodo' => $fecha_aux->format('Y-m'),
                'fecha_vencimiento' => $vencimiento->format('Y-m-d'),
                'monto_original' => $contrato->monto_alquiler,
                'saldo_pendiente' => $contrato->monto_alquiler,
                'estado' => 'PENDIENTE'
            ]);

            $fecha_aux->addMonth();
            $numero_cuota++;
        }

        return response()->json(['message' => 'Contrato creado con garantes', 'contrato' => $contrato]);
    }
    public function index()
    {
        return Contrato::with(['inquilino', 'propiedad', 'garantes'])
                    ->orderBy('id', 'desc') 
                    ->get();
    }
    public function finalizar($id)
    {
        $contrato = Contrato::find($id);
    
        if (!$contrato) {
            return response()->json(['message' => 'Contrato no encontrado'], 404);
        }
    
        $contrato->activo = false; // Lo marcamos como finalizado
        $contrato->save();
    
        return response()->json(['mensaje' => 'Contrato finalizado correctamente']);
    }
}