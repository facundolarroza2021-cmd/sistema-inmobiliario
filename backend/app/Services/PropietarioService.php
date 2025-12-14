<?php

namespace App\Services;

use App\Models\Propietario;
use App\DTOs\PropietarioData; 

class PropietarioService
{
    public function listarTodos()
    {
        return Propietario::all();
    }

    public function obtenerDetalleConMetricas(int $id): array
    {
        $propietario = Propietario::with([
            'propiedades.contratoActivo.inquilino',
            'liquidaciones'
        ])->findOrFail($id);

        $totalPropiedades = $propietario->propiedades->count();
        $propiedadesAlquiladas = $propietario->propiedades->whereNotNull('contratoActivo')->count();
        
        $tasaOcupacion = $totalPropiedades > 0 
            ? round(($propiedadesAlquiladas / $totalPropiedades) * 100, 1) 
            : 0;

        return [
            'datos' => $propietario,
            'kpis' => [
                'total_propiedades' => $totalPropiedades,
                'ocupacion' => $propiedadesAlquiladas,
                'tasa_ocupacion_porcentaje' => $tasaOcupacion,
                'saldo_pendiente' => 0
            ]
        ];
    }

    public function crearPropietario(PropietarioData $datos): Propietario
    {
        return Propietario::create($datos->toArray());
    }

    public function actualizarPropietario(int $id, PropietarioData $datos): Propietario
    {
        $propietario = Propietario::findOrFail($id);
        
        $dataArray = array_filter($datos->toArray(), fn($v) => !is_null($v));
        
        $propietario->update($dataArray);
        
        return $propietario;
    }

    public function eliminarPropietario(int $id): void
    {
        $propietario = Propietario::findOrFail($id);
        $propietario->delete();
    }
}