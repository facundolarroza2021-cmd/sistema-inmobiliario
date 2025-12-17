<?php

namespace Database\Factories;

use App\Models\Contrato;
use App\Models\Inquilino;
use App\Models\Propiedad;
use App\Enums\ContratoEstado;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ContratoFactory extends Factory
{
    protected $model = Contrato::class;

    public function definition(): array
    {
        return [
            'inquilino_id' => Inquilino::factory(),
            'propiedad_id' => Propiedad::factory(),
            'monto_alquiler' => $this->faker->randomFloat(2, 500, 15000),
            'fecha_inicio' => Carbon::now()->subMonths(6)->toDateString(),
            'fecha_fin' => Carbon::now()->addMonths(18)->toDateString(),
            'dia_vencimiento' => $this->faker->numberBetween(1, 10),
            'estado' => ContratoEstado::ACTIVO,
            'archivo_url' => null,
        ];
    }

    // Opcional: define un estado para contratos finalizados
    public function finalizado(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'estado' => ContratoEstado::FINALIZADO,
            'fecha_fin' => Carbon::now()->subDay()->toDateString(),
        ]);
    }
}