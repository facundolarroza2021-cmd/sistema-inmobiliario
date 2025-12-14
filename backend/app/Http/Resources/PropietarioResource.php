<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropietarioResource extends JsonResource
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
                'cbu' => $this->cbu,
            ],

            'kpis' => $this->when(isset($this->kpis), function () {
                return $this->kpis; 
            }),
            'fecha_registro' => $this->created_at->format('Y-m-d'),
        ];
    }
}