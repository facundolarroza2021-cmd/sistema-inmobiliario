<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Propietario; // Importante

class PropiedadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'direccion' => $this->faker->address(),
            'tipo' => $this->faker->randomElement(['Casa', 'Departamento', 'Local', 'Oficina']),
            //'precio' => $this->faker->numberBetween(50000, 500000),
            'estado' => 'DISPONIBLE',
            'propietario_id' => Propietario::factory(), 
        ];
    }
}