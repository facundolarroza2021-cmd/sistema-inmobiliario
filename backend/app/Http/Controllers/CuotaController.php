<?php

namespace App\Http\Controllers;

use App\Services\CuotaService;
use App\Models\Cuota;

class CuotaController extends Controller
{
    protected $cuotaService;

    public function __construct(CuotaService $cuotaService)
    {
        $this->cuotaService = $cuotaService;
    }

    public function index()
    {
        return response()->json($this->cuotaService->listarCuotas());
    }
    public function getDeudasPendientes()
    {
        // Carga las cuotas que no están PAGADAS (PENDIENTE o PARCIAL)
        // Carga las relaciones que necesitas para mostrar en la tabla (inquilino, propiedad)
        $deudas = Cuota::where('estado', '!=', 'PAGADA')
                        ->with([
                            'pagos',
                            'contrato' => function ($query) {
                                $query->with('inquilino', 'propiedad');
                            }
                        ])
                        ->orderBy('fecha_vencimiento')
                        ->get();

        return response()->json($deudas);
    }
}
