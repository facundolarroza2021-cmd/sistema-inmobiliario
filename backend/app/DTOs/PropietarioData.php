<?php

namespace App\DTOs;

class PropietarioData
{
    public function __construct(
        public string $nombre_completo,
        public string $dni,
        public ?string $email = null,
        public ?string $telefono = null,
        public ?string $cbu = null,
    ) {}

    /**
     * Fábrica: Crea el DTO a partir de un array (normalmente del Request)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombre_completo: $data['nombre_completo'],
            dni: $data['dni'],
            email: $data['email'] ?? null,
            telefono: $data['telefono'] ?? null,
            cbu: $data['cbu'] ?? null,
        );
    }

    /**
     * Convierte el DTO a array para que Eloquent lo pueda guardar
     */
    public function toArray(): array
    {
        return [
            'nombre_completo' => $this->nombre_completo,
            'dni' => $this->dni,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'cbu' => $this->cbu,
        ];
    }
}