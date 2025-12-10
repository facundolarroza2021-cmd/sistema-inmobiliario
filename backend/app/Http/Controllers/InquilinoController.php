<?php

namespace App\Http\Controllers;

use App\Models\Inquilino;
use Illuminate\Http\Request;

/**
 * Class InquilinoController
 * * CRUD básico para la gestión de inquilinos.
 * La lógica compleja de inquilinos suele delegarse a los Contratos.
 */

class InquilinoController extends Controller
{
    public function index()
    {
        return Inquilino::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required',
            'dni' => 'required|unique:inquilinos',
            'telefono' => 'required'
        ]);

        $inquilino = Inquilino::create($request->all());

        return response()->json($inquilino, 201);
    }
    public function update(Request $request, $id){
        $inquilino = Inquilino::find($id);
        if (!$inquilino) return response()->json(['message' => 'No encontrado'], 404);

        $request->validate([
            'nombre_completo' => 'required',
            'dni' => 'required|unique:inquilinos,dni,' . $id // Excluir su propio ID de la validación
        ]);

        $inquilino->update($request->all());
        return response()->json($inquilino);
    }

    public function destroy($id){
        $inquilino = Inquilino::find($id);
        if (!$inquilino) return response()->json(['message' => 'No encontrado'], 404);

        $inquilino->delete();
        return response()->json(['message' => 'Inquilino eliminado']);
    }
}