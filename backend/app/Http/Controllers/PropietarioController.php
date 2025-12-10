<?php

namespace App\Http\Controllers;

use App\Models\Propietario;
use Illuminate\Http\Request;

class PropietarioController extends Controller
{
    /**
     * Retorna lista simple de propietarios.
     * Útil para selectores/dropdowns en la UI.
     */
    public function index()
    {
        return Propietario::all();
    }

    /**
     * Crea un nuevo propietario.
     * Valida unicidad de DNI.
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'nombre_completo' => 'required',
            'dni' => 'required|unique:propietarios', 
            'email' => 'nullable|email'
        ]);

        $propietario = Propietario::create($request->all());

        return response()->json($propietario, 201);
    }
    /**
     * Muestra el detalle completo de un propietario.
     * * Carga relaciones anidadas (Eager Loading):
     * - Propiedades -> Contrato Activo -> Inquilino
     * - Liquidaciones históricas
     * * Calcula KPIs básicos (Total propiedades, Tasa de ocupación).
     *
     * @param int $id ID del propietario.
     * @return \Illuminate\Http\JsonResponse Datos del propietario + KPIs.
     */
    public function show($id)
    {
        
        $propietario = Propietario::with([
            'propiedades.contratoActivo.inquilino', 
            'liquidaciones'
        ])->findOrFail($id);

        $totalPropiedades = $propietario->propiedades->count();
        $propiedadesAlquiladas = $propietario->propiedades->whereNotNull('contratoActivo')->count();

        return response()->json([
            'datos' => $propietario,
            'kpis' => [
                'total_propiedades' => $totalPropiedades,
                'ocupacion' => $propiedadesAlquiladas,
                'saldo_pendiente' => 0 
            ]
        ]);
    }
    /**
     * Actualiza datos del propietario.
     * * Nota: La validación 'unique' excluye el ID actual para permitir
     * guardar cambios sin que salte error de "DNI ya existe" sobre sí mismo.
     */
    public function update(Request $request, $id)
    {
        $propietario = Propietario::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'required|string',
            'dni' => 'required|unique:propietarios,dni,'.$id,
            'email' => 'required|email|unique:propietarios,email,'.$id,
            'telefono' => 'nullable',
            'cbu' => 'nullable'
        ]);

        $propietario->update($request->all());

        return response()->json($propietario);
    }
    public function destroy($id)
    {
        $propietario = Propietario::findOrFail($id);
        
        $propietario->delete();

        return response()->json(['message' => 'Propietario y todos sus datos eliminados correctamente.']);
    }
}
