<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\InquilinoResource; // Reutilizamos InquilinoResource si existe
use App\Http\Resources\PropiedadResource; // Reutilizamos PropiedadResource si existe

class ContratoResource extends JsonResource
{
    /**
     * Transforma el recurso a un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inquilino' => new InquilinoResource($this->whenLoaded('inquilino')), // Usa el Resource para el inquilino
            'propiedad' => new PropiedadResource($this->whenLoaded('propiedad')), // Usa el Resource para la propiedad
            'monto_alquiler' => (float) $this->monto_alquiler,
            'fecha_inicio' => $this->fecha_inicio?->toDateString(),
            'fecha_fin' => $this->fecha_fin?->toDateString(),
            'dia_vencimiento' => (int) $this->dia_vencimiento,
            'meses' => (int) $this->meses,
            'estado' => $this->estado,
            'archivo_url' => $this->archivo_url ? asset('storage/' . $this->archivo_url) : null,
            'garantes' => GaranteResource::collection($this->whenLoaded('garantes')), // Si existe GaranteResource
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}