<?php

namespace App\Http\Controllers;

use App\Services\PagoService;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    protected $pagoService;

    public function __construct(PagoService $pagoService)
    {
        $this->pagoService = $pagoService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto' => 'required|numeric|min:1',
            'forma_pago' => 'required|string'
        ]);

        try {
            $resultado = $this->pagoService->registrarPago(
                $request->cuota_id,
                $request->monto,
                $request->forma_pago
            );

            return response()->json([
                'mensaje' => 'Pago registrado correctamente',
                'detalle' => $resultado
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}