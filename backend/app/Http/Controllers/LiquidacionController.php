<?php

namespace App\Http\Controllers;

use App\Models\Liquidacion;
use App\Models\Cuota;
use App\Models\Propietario;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LiquidacionController extends Controller
{
    // 1. CALCULAR (Previsualizar)
    public function previsualizar($propietarioId)
    {
        // 1. Obtener cuotas pagadas y no liquidadas
        $cuotas = Cuota::whereHas('contrato.propiedad', function($q) use ($propietarioId) {
                $q->where('propietario_id', $propietarioId);
            })
            ->where('estado', 'PAGADA')
            ->whereNull('liquidacion_id') // Solo las que no han sido liquidadas
            ->with(['contrato.inquilino', 'contrato.propiedad'])
            ->get();

        // 2. Realizar los cálculos matemáticos
        $totalIngresos = $cuotas->sum('monto_original'); // Suma directa de Laravel
        $porcentajeComision = 10; // Esto podrías traerlo del modelo Propietario o Configuración
        $montoComision = $totalIngresos * ($porcentajeComision / 100);
        $totalGastos = $cuotas->sum('monto_gastos'); // Si tienes gastos administrativos
        $totalNeto = $totalIngresos - $montoComision - $totalGastos;

        // 3. Determinar el estado de la liquidación (Borrador/Pendiente)
        $estadoLiquidacion = $totalNeto > 0 ? 'PENDIENTE DE PAGO' : 'SIN SALDO';

        // 4. Estructurar la respuesta (Mapping)
        // Aquí es donde hacemos que se vea "lindo" y ordenado
        $respuesta = [
            'informacion_general' => [
                'propietario_id' => $propietarioId,
                'estado_liquidacion' => $estadoLiquidacion, // Tu nuevo campo solicitado
                'fecha_emision' => now()->format('d/m/Y H:i'),
            ],
            'items_a_liquidar' => $cuotas->map(function($cuota) {
                return [
                    'cuota_id' => $cuota->id,
                    'periodo' => $cuota->periodo, // ej: 2027-07
                    'propiedad' => $cuota->contrato->propiedad->direccion ?? 'Sin dirección',
                    'inquilino' => $cuota->contrato->inquilino->nombre_completo,
                    'vencimiento' => \Carbon\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y'),
                    'monto' => number_format($cuota->monto_original, 2, ',', '.'), // Formato moneda
                ];
            }),
            'resumen_financiero' => [
                'total_cobrado' => number_format($totalIngresos, 2, ',', '.'),
                'tasa_comision' => $porcentajeComision . '%',
                'monto_comision' => number_format($montoComision, 2, ',', '.'),
                'total_gastos' => number_format($totalGastos, 2, ',', '.'),
                'total_neto_a_transferir' => number_format($totalNeto, 2, ',', '.')
            ]
        ];

        return response()->json($respuesta);
    }

    // 2. GUARDAR y GENERAR PDF
    public function store(Request $request)
    {
        $request->validate([
            'propietario_id' => 'required',
            'cuotas_ids' => 'required|array',
            'total_ingresos' => 'required|numeric',
            'monto_neto' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // A. Guardar Liquidación
            $liquidacion = Liquidacion::create([
                'propietario_id' => $request->propietario_id,
                'periodo' => now()->format('m-Y'),
                'fecha_liquidacion' => now(),
                'total_ingresos' => $request->total_ingresos,
                'total_gastos' => $request->total_gastos ?? 0,
                'comision_inmobiliaria' => $request->comision,
                'monto_neto' => $request->monto_neto,
                'observaciones' => $request->observaciones,
            ]);

            // B. Actualizar Cuotas
            Cuota::whereIn('id', $request->cuotas_ids)
                ->update(['liquidacion_id' => $liquidacion->id]);

            // C. Salida de Caja
            $propietario = Propietario::find($request->propietario_id);
            
            // Verificamos si existe usuario logueado, sino asignamos null o 1
            $userId = Auth::id() ?? 1;

            Caja::create([
                'tipo' => 'EGRESO',
                'concepto' => "Liquidación #{$liquidacion->id} - {$propietario->nombre} {$propietario->apellido}",
                'monto' => $request->monto_neto,
                'fecha' => now(),
                'usuario_id' => $userId
            ]);

            // D. PDF
            $pdfUrl = null;
            try {
                $data = [
                    'liquidacion' => $liquidacion,
                    'propietario' => $propietario,
                    'detalles'    => Cuota::whereIn('id', $request->cuotas_ids)
                                    ->with(['contrato.inquilino','contrato.propiedad'])
                                    ->get(),
                    'fecha'       => now()
                ];
                
                $pdf = Pdf::loadView('pdf.liquidacion', $data);
                $nombreArchivo = 'liquidaciones/liq_' . $liquidacion->id . '_' . time() . '.pdf';
                Storage::disk('public')->put($nombreArchivo, $pdf->output());
                $pdfUrl = asset('storage/' . $nombreArchivo);
            } catch (\Exception $e) {
                Log::error("Error PDF: " . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'message' => 'Liquidación exitosa',
                'url_pdf' => $pdfUrl
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // 3. LISTAR HISTORIAL
    public function index() {
        return response()->json(Liquidacion::with('propietario')->latest()->get());
    }
}