<?php

namespace App\Http\Controllers;

use App\Models\Propiedad;
use Illuminate\Http\Request;
use App\Models\PropiedadImagen;

class PropiedadController extends Controller
{
    public function index()
    {
        return Propiedad::with('propietario')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'propietario_id' => 'required|exists:propietarios,id', 
            'direccion' => 'required',
            'tipo' => 'required', 
            'comision' => 'required|numeric' 
        ]);

        $propiedad = Propiedad::create($request->all());

        return response()->json($propiedad, 201);
    }
        public function show($id)
    {
        $propiedad = Propiedad::with(['propietario', 'contratos.inquilino', 'imagenes'])->findOrFail($id);

        // URLs completas para las fotos
        foreach($propiedad->imagenes as $img) {
            $img->url = asset('storage/' . $img->ruta_archivo);
        }

        return response()->json($propiedad);
    }

    public function uploadFoto(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|max:5120' // Max 5MB
        ]);

        $propiedad = Propiedad::findOrFail($id);

        if ($request->hasFile('imagen')) {
            // Guardamos en storage/app/public/propiedades
            $path = $request->file('imagen')->store('propiedades', 'public');

            // Guardamos en BD
            $img = PropiedadImagen::create([
                'propiedad_id' => $propiedad->id,
                'ruta_archivo' => $path
            ]);

            return response()->json([
                'mensaje' => 'Foto subida',
                'url' => asset('storage/' . $path),
                'id' => $img->id
            ]);
        }

        return response()->json(['error' => 'No se envió imagen'], 400);
    }
    // Actualizar
    public function update(Request $request, $id)
    {
        $propiedad = Propiedad::findOrFail($id);
        
        $propiedad->update([
            'direccion' => $request->direccion,
            'tipo' => $request->tipo,
            'propietario_id' => $request->propietario_id,
            'comision' => $request->comision
        ]);

        $propiedad->update($request->all());
        return response()->json($propiedad);
    }

    // Eliminar (Soft Delete)
    public function destroy($id)
    {
        $propiedad = Propiedad::findOrFail($id);
        $propiedad->delete(); // Laravel pondrá la fecha de borrado automáticamente
        return response()->json(['message' => 'Propiedad eliminada']);
    }
}