<?php

namespace App\Services;

use App\Models\Inquilino;
use App\DTOs\InquilinoData;

class InquilinoService
{
    public function listarTodos()
    {
        return Inquilino::all();
    }

    public function crearInquilino(array $datos): Inquilino
    {
        return Inquilino::create($datos);
    }

    public function actualizarInquilino(int $id, InquilinoData $datos): Inquilino
    {
        $inquilino = Inquilino::findOrFail($id);
        
        // Filtramos nulos para actualizaciones parciales
        $dataArray = array_filter($datos->toArray(), fn($v) => !is_null($v));
        
        $inquilino->update($dataArray);
        
        return $inquilino;
    }

    public function eliminarInquilino(int $id): void
    {
        $inquilino = Inquilino::findOrFail($id);
        $inquilino->delete();
    }
}
