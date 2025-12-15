<?php

namespace App\Services;

use App\Models\Gasto;
use App\Models\Liquidacion;
use App\Models\Propietario;
use App\Models\Cuota; // Importar Cuota para el cálculo de ingresos
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * Servicio de Liquidación: Gestiona la generación del informe de pago
 * al propietario, descontando comisiones y gastos pendientes.
 */
class LiquidacionService
{
    protected $gastoService;

    public function __construct(GastoService $gastoService)
    {
        $this->gastoService = $gastoService;
    }

    /**
     * Genera la liquidación final para un propietario en un período específico.
     *
     * **Flujo:**
     * 1. Calcula ingresos (cuotas PAGADAS y no liquidadas).
     * 2. Recupera todos los gastos pendientes del propietario.
     * 3. Calcula el monto final a pagar (Ingresos - Comisión - Gastos).
     * 4. Crea el registro Liquidacion.
     * 5. Marca los gastos como liquidados (vinculándolos a esta Liquidación).
     *
     * @param int $propietarioId
     * @param string $periodo Formato 'YYYY-MM'
     * @return Liquidacion
     * @throws Exception Si el saldo es negativo o no hay ingresos.
     */
    public function generarLiquidacion(int $propietarioId, string $periodo): Liquidacion
    {
        return DB::transaction(function () use ($propietarioId, $periodo) {

            $propietario = Propietario::with('propiedades')->findOrFail($propietarioId);
            $idsPropiedades = $propietario->propiedades->pluck('id')->toArray();
            
            // --- CÁLCULO DE INGRESOS ---
            // Asumimos que el ingreso son cuotas PAGADAS que aún no se han liquidado.
            $cuotasLiquidadas = Cuota::whereHas('contrato', function ($q) use ($idsPropiedades) {
                $q->whereIn('propiedad_id', $idsPropiedades);
            })
            ->where('estado', 'PAGADO')
            ->whereNull('liquidacion_id') // Cuotas que no han sido liquidadas
            ->where('periodo', '<=', $periodo) // Liquida hasta el período seleccionado
            ->get();
            
            // Suma de los importes originales de las cuotas
            $totalIngresos = $cuotasLiquidadas->sum('importe'); 

            if ($totalIngresos <= 0) {
                throw new Exception('No hay ingresos PAGADOS pendientes para liquidar en este período.');
            }
            
            // --- CÁLCULO DE DESCUENTOS ---
            $comision = $totalIngresos * 0.10; // 10% de comisión (asumiendo valor fijo)
            
            $gastosPendientes = $this->gastoService->obtenerGastosPendientesParaLiquidacion($idsPropiedades);
            $totalGastos = $gastosPendientes->sum('monto');

            // Monto a pagar (Ingreso - Comisión - Gastos)
            $montoPagar = $totalIngresos - $comision - $totalGastos;

            if ($montoPagar < 0) {
                throw new Exception('El saldo a pagar es negativo ($'.number_format($montoPagar, 2).'). No se puede liquidar.');
            }
            
            // --- CREACIÓN DEL REGISTRO LIQUIDACION ---
            $liquidacion = Liquidacion::create([
                'propietario_id' => $propietario->id,
                'periodo' => $periodo,
                'monto_total' => $totalIngresos, // Ingreso Bruto
                'monto_comision' => $comision,
                'monto_gastos' => $totalGastos, // Descuento de Gastos
                'monto_entregado' => $montoPagar, // Monto Neto a transferir
            ]);

            // --- VINCULACIÓN DE ITEMS A LA LIQUIDACIÓN ---
            
            // 1. Marcar Gastos como liquidados
            $gastosPendientes->each(function ($gasto) use ($liquidacion) {
                $gasto->liquidacion_id = $liquidacion->id;
                $gasto->save();
            });
            
            // 2. Marcar Cuotas como liquidadas (para no incluirlas en futuras liquidaciones)
            $cuotasLiquidadas->each(function ($cuota) use ($liquidacion) {
                $cuota->liquidacion_id = $liquidacion->id;
                $cuota->save();
            });

            return $liquidacion;
        });
    }

    /**
     * Lista todas las liquidaciones registradas.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listarLiquidaciones()
    {
        return Liquidacion::with('propietario')->orderBy('id', 'desc')->get();
    }
}