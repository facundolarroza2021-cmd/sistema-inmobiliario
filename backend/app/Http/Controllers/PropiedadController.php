<?php

namespace App\Http\Controllers;

use App\Services\PropiedadService;
use Illuminate\Http\Request;

class PropiedadController extends Controller
{
    protected $propiedadService;

    public function __construct(PropiedadService $propiedadService)
    {
        $this->propiedadService = $propiedadService;
    }

    public function index()
    {
        return response()->json($this->propiedadService->listarTodo());
    }

    public function show($id)
    {
        return response()->json($this->propiedadService->obtenerDetalle($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'propietario_id' => 'required|exists:propietarios,id',
            'direccion' => 'required|string',
            'tipo' => 'required|string',
            'precio_alquiler' => 'required|numeric',
            'comision' => 'required|numeric'
        ]);

        $propiedad = $this->propiedadService->crearPropiedad($validated);
        return response()->json($propiedad, 201);
    }

    public function uploadFoto(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|max:5120'
        ]);

        $resultado = $this->propiedadService->subirFoto($id, $request->file('imagen'));
        
        return response()->json(['mensaje' => 'Foto subida', ...$resultado]);
    }

    public function destroy($id)
    {
        $this->propiedadService->eliminarPropiedad($id);
        return response()->json(['message' => 'Propiedad eliminada']);
    }
}