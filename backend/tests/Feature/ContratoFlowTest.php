<?php

namespace Tests\Feature;

use App\Models\Inquilino;
use App\Models\Propiedad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ContratoFlowTest extends TestCase
{
    use RefreshDatabase; 

    public function test_crear_contrato_genera_cuotas_y_ocupa_propiedad()
    {
        $user = User::factory()->create();
        $inquilino = Inquilino::factory()->create();
        $propiedad = Propiedad::factory()->create(['estado' => 'DISPONIBLE']);

        $payload = [
            'inquilino_id' => $inquilino->id,
            'propiedad_id' => $propiedad->id,
            'monto_actual' => 50000,
            'fecha_inicio' => '2025-01-01',
            'meses' => 12,
            'dia_vencimiento' => 10,
            'garantes' => [] 
        ];

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->postJson('/api/contratos', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contratos', [
            'inquilino_id' => $inquilino->id,
            'estado' => 'ACTIVO'
        ]);

        // C) Verificar que la propiedad cambió a OCUPADO
        $this->assertDatabaseHas('propiedades', [
            'id' => $propiedad->id,
            'estado' => 'OCUPADO'
        ]);

        // D) Verificar que se crearon 12 cuotas
        $this->assertDatabaseCount('cuotas', 12);
    }
}