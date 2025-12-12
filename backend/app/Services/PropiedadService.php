<?php

namespace App\Services;

use App\Models\Propiedad;
use App\Models\PropiedadImagen;
use Illuminate\Http\UploadedFile;

class PropiedadService
{
    public function listarTodo()
    {
        return Propiedad::with('propietario')->get();
    }

    public function obtenerDetalle(int $id)
    {
        $propiedad = Propiedad::with(['propietario', 'contratos.inquilino', 'imagenes'])->findOrFail($id);
        
        
        /** @var \App\Models\Propiedad $propiedad */

       
        
        foreach ($propiedad->imagenes as $img) {
            /** @var \App\Models\PropiedadImagen $img */
            $img->url = asset('storage/' . $img->ruta_archivo);
        }
        
        return $propiedad;
    }

    public function crearPropiedad(array $datos): Propiedad
    {
        return Propiedad::create($datos);
    }

    public function subirFoto(int $id, UploadedFile $archivo)
    {
        $propiedad = Propiedad::findOrFail($id);

        $path = $archivo->store('propiedades', 'public');

        $imagen = PropiedadImagen::create([
            'propiedad_id' => $propiedad->id,
            'ruta_archivo' => $path,
        ]);

        return [
            'id' => $imagen->id,
            'url' => asset('storage/'.$path),
        ];
    }

    public function eliminarPropiedad(int $id): void
    {
        $propiedad = Propiedad::findOrFail($id);
        $propiedad->delete();
    }
}
