<?php

namespace App\Http\Controllers;

use App\Models\Propiedad;
use Illuminate\Http\Request;
use App\Models\PropiedadImagen;

class PropiedadController extends Controller
{
    /**
     * Retorna lista de propiedades con propietario.
     */
    public function index()
    {
        return Propiedad::with('propietario')->get();
    }

    /**
     * Crea una nueva propiedad.
     */
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
    /**
     * Muestra una propiedad con propietario, contratos e imágenes.
     */
    public function show($id)
    {
        $propiedad = Propiedad::with(['propietario', 'contratos.inquilino', 'imagenes'])->findOrFail($id);

        foreach($propiedad->imagenes as $img) {
            $img->url = asset('storage/' . $img->ruta_archivo);
        }

        return response()->json($propiedad);
    }
    /**
     * Sube una foto y la asocia a una propiedad.
     *
     * Almacenamiento: disco 'public' -> carpeta 'propiedades'.
     * Restricción: Imágenes (jpg, png, etc) máximo 5MB.
     *
     * @param Request $request Debe contener archivo en campo 'imagen'.
     * @param int $id ID de la propiedad.
     * @return \Illuminate\Http\JsonResponse URL de la imagen subida.
     */

    public function uploadFoto(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|max:5120' // Max 5MB
        ]);

        $propiedad = Propiedad::findOrFail($id);

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('propiedades', 'public');

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
    
    /**
     * Actualiza datos de una propiedad.
     */

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

    /**
     * Elimina una propiedad (Soft Delete).
     */
    public function destroy($id)
    {
        $propiedad = Propiedad::findOrFail($id);
        $propiedad->delete(); 
        return response()->json(['message' => 'Propiedad eliminada']);
    }
}