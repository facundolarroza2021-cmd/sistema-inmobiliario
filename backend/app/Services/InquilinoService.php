<?php

namespace App\Services;

use App\Models\Inquilino;

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

    public function actualizarInquilino(int $id, array $datos): Inquilino
    {
        $inquilino = Inquilino::findOrFail($id);
        $inquilino->update($datos);
        return $inquilino;
    }

    public function eliminarInquilino(int $id): void
    {
        $inquilino = Inquilino::findOrFail($id);
        $inquilino->delete();
    }
}