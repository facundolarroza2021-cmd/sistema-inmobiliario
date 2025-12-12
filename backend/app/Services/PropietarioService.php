<?php

namespace App\Services;

use App\Models\Propietario;

class PropietarioService
{
    public function listarTodos()
    {
        return Propietario::all();
    }

    /**
     * Obtiene el detalle completo y calcula métricas (KPIs).
     */
    public function obtenerDetalleConMetricas(int $id): array
    {
        $propietario = Propietario::with([
            'propiedades.contratoActivo.inquilino',
            'liquidaciones',
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
                'saldo_pendiente' => 0,
            ],
        ];
    }

    public function crearPropietario(array $datos): Propietario
    {
        return Propietario::create($datos);
    }

    public function actualizarPropietario(int $id, array $datos): Propietario
    {
        $propietario = Propietario::findOrFail($id);
        $propietario->update($datos);

        return $propietario;
    }

    public function eliminarPropietario(int $id): void
    {
        $propietario = Propietario::findOrFail($id);
        $propietario->delete();
    }
}
