<?php

namespace App\Http\Controllers;

use App\Models\Propietario;
use Illuminate\Http\Request;

class PropietarioController extends Controller
{
    // Listar todos (Para el dropdown del frontend)
    public function index()
    {
        return Propietario::all();
    }

    // Crear nuevo
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
    public function show($id)
    {
        // Buscamos al dueño
        $propietario = Propietario::with([
            // Traer sus propiedades
            'propiedades.contratoActivo.inquilino', 
            // Traer sus liquidaciones (pagos que ya le hicimos)
            'liquidaciones'
        ])->findOrFail($id);

        // Cálculo rápido de KPIs (Opcional por ahora)
        $totalPropiedades = $propietario->propiedades->count();
        $propiedadesAlquiladas = $propietario->propiedades->whereNotNull('contratoActivo')->count();

        return response()->json([
            'datos' => $propietario,
            'kpis' => [
                'total_propiedades' => $totalPropiedades,
                'ocupacion' => $propiedadesAlquiladas,
                // Aquí en el futuro calcularemos la deuda real
                'saldo_pendiente' => 0 
            ]
        ]);
    }
    public function update(Request $request, $id)
    {
        $propietario = Propietario::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'required|string',
            // VALIDACIÓN INTELIGENTE: Unique ignorando al ID actual
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
