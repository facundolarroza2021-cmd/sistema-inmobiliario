<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InquilinoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre_completo,
            'identificacion' => $this->dni,
            'contacto' => [
                'email' => $this->email,
                'telefono' => $this->telefono,
            ],
            // Agrupamos la info del garante para que el JSON quede más limpio
            'garante' => [
                'nombre' => $this->garante_nombre,
                'identificacion' => $this->garante_dni,
            ],
            'fecha_registro' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}