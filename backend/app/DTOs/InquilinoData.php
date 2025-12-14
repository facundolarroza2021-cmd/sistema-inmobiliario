<?php

namespace App\DTOs;

class InquilinoData
{
    public function __construct(
        public string $nombre_completo,
        public string $dni,
        public ?string $email = null,
        public ?string $telefono = null,
        public ?string $garante_nombre = null,
        public ?string $garante_dni = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombre_completo: $data['nombre_completo'],
            dni: $data['dni'],
            email: $data['email'] ?? null,
            telefono: $data['telefono'] ?? null,
            garante_nombre: $data['garante_nombre'] ?? null,
            garante_dni: $data['garante_dni'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'nombre_completo' => $this->nombre_completo,
            'dni' => $this->dni,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'garante_nombre' => $this->garante_nombre,
            'garante_dni' => $this->garante_dni,
        ];
    }
}