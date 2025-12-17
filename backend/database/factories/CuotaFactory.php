<?php

namespace Database\Factories;

use App\Models\Cuota;
use App\Models\Contrato;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CuotaFactory extends Factory
{
    protected $model = Cuota::class;

    public function definition(): array
    {
        $periodo = Carbon::now()->addMonths($this->faker->numberBetween(1, 12));
        $monto = $this->faker->randomFloat(2, 500, 15000);

        return [
            'contrato_id' => Contrato::factory(),
            'numero_cuota' => $this->faker->numberBetween(1, 36), // <-- AÑADIDO
            'periodo' => $periodo->format('Y-m'),
            'fecha_vencimiento' => $periodo->setDay(5)->toDateString(), // <-- CORREGIDO
            'monto_original' => $monto, // <-- CORREGIDO
            'saldo_pendiente' => $monto,
            'estado' => 'PENDIENTE', 
            // Las columnas 'notas' y 'fecha_pago' no están en la migración original,
            // pero si existen en tu base de datos real, mantenlas en el modelo.
        ];
    }

    /**
     * Define un estado de cuota pagada.
     */
    public function pagada(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'PAGADO',
            'fecha_pago' => Carbon::now()->toDateString(),
            'saldo_pendiente' => 0.00,
        ]);
    }
}