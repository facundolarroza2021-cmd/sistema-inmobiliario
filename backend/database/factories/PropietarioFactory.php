<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PropietarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_completo' => $this->faker->name(),
            'dni' => $this->faker->unique()->numerify('########'),
            'email' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'cbu' => $this->faker->creditCardNumber(),
        ];
    }
}