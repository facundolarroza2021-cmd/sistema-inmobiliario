<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InquilinoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_completo' => $this->faker->name(),
            'dni' => $this->faker->unique()->numerify('########'), // Genera 8 números aleatorios
            'email' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'garante_nombre' => $this->faker->name(),
            'garante_dni' => $this->faker->numerify('########'),
        ];
    }
}