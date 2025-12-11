<?php

namespace App\Http\Controllers;

use App\Services\InquilinoService;
use Illuminate\Http\Request;

class InquilinoController extends Controller
{
    protected $inquilinoService;

    public function __construct(InquilinoService $inquilinoService)
    {
        $this->inquilinoService = $inquilinoService;
    }

    public function index()
    {
        return response()->json($this->inquilinoService->listarTodos());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string',
            'dni' => 'required|string|unique:inquilinos',
            'telefono' => 'required|string',
            'email' => 'nullable|email'
        ]);

        $inquilino = $this->inquilinoService->crearInquilino($validated);
        return response()->json($inquilino, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string',
            'dni' => 'required|string|unique:inquilinos,dni,' . $id,
            'telefono' => 'required|string',
            'email' => 'nullable|email'
        ]);

        $inquilino = $this->inquilinoService->actualizarInquilino($id, $validated);
        return response()->json($inquilino);
    }

    public function destroy($id)
    {
        $this->inquilinoService->eliminarInquilino($id);
        return response()->json(['message' => 'Inquilino eliminado correctamente']);
    }
}