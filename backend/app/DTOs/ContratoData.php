<?php

namespace App\DTOs;

class ContratoData
{
    public function __construct(
        public int $inquilino_id,
        public int $propiedad_id,
        public float $monto_alquiler,
        public string $fecha_inicio,
        public int $meses,
        public int $dia_vencimiento,
        public array $garantes = []
    ) {}

    public static function fromArray(array $data): self
    {
        $garantes = $data['garantes'] ?? [];
        if (is_string($garantes)) {
            $garantes = json_decode($garantes, true) ?? [];
        }

        return new self(
            inquilino_id: (int) $data['inquilino_id'],
            propiedad_id: (int) $data['propiedad_id'],
            monto_alquiler: (float) ($data['monto_actual'] ?? $data['monto_alquiler']),
            fecha_inicio: $data['fecha_inicio'],
            meses: (int) ($data['meses'] ?? 12),
            dia_vencimiento: (int) $data['dia_vencimiento'],
            garantes: $garantes
        );
    }

    public function toArray(): array
    {
        return [
            'inquilino_id' => $this->inquilino_id,
            'propiedad_id' => $this->propiedad_id,
            'monto_alquiler' => $this->monto_alquiler,
            'fecha_inicio' => $this->fecha_inicio,
            'meses' => $this->meses,
            'dia_vencimiento' => $this->dia_vencimiento,
            'garantes' => $this->garantes,
        ];
    }
}