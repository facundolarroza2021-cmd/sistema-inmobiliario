<?php

namespace App\Http\Controllers;

use App\Services\ContratoService;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    protected $contratoService;

    public function __construct(ContratoService $contratoService)
    {
        $this->contratoService = $contratoService;
    }

    public function index()
    {
        return response()->json($this->contratoService->listarContratos());
    }

    public function store(Request $request)
    {
        // Validamos aquí (HTTP Layer)
        $request->validate([
            'inquilino_id' => 'required|exists:inquilinos,id',
            'propiedad_id' => 'required|exists:propiedades,id',
            'monto_actual' => 'required|numeric',
            'fecha_inicio' => 'required|date',
            'dia_vencimiento' => 'required|integer|min:1|max:31',
            'meses' => 'nullable|integer|min:1',
            'archivo' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'garantes' => 'nullable',
        ]);

        try {
            $contrato = $this->contratoService->crearContratoCompleto(
                $request->all(),
                $request->file('archivo')
            );

            return response()->json([
                'message' => 'Contrato creado exitosamente',
                'contrato' => $contrato,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear contrato: '.$e->getMessage()], 500);
        }
    }

    public function finalizar($id)
    {
        try {
            $this->contratoService->finalizarContrato($id);

            return response()->json(['message' => 'Contrato finalizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se pudo finalizar el contrato'], 404);
        }
    }
}
