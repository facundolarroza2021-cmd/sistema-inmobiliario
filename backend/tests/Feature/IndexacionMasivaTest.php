<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contrato;
use App\Models\Cuota;
use App\Models\Propiedad;
use App\Models\Inquilino;
use App\Enums\ContratoEstado;
use Carbon\Carbon;

class IndexacionMasivaTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        // 1. Crear un usuario administrador para la autenticación
        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Helper para crear un contrato activo con cuotas futuras.
     * @param int $mesesFuturos Número de cuotas futuras a crear.
     * @return Contrato
     */
    protected function crearContratoConCuotas(int $mesesFuturos = 6, float $montoInicial = 1000.00): Contrato
    {
        // Usamos Factories para crear la estructura base
        $propiedad = Propiedad::factory()->create(['estado' => 'OCUPADO']);
        $inquilino = Inquilino::factory()->create();

        $contrato = Contrato::factory()->create([
            'inquilino_id' => $inquilino->id,
            'propiedad_id' => $propiedad->id,
            'monto_alquiler' => $montoInicial,
            'fecha_inicio' => Carbon::now()->subMonths(3)->format('Y-m-d'), // Contrato activo
            'fecha_fin' => Carbon::now()->addMonths($mesesFuturos + 3)->format('Y-m-d'),
            'estado' => ContratoEstado::ACTIVO,
            'dia_vencimiento' => 5,
        ]);
        
        // Simular la generación de cuotas futuras (desde el mes siguiente)
        $mesActual = Carbon::now()->startOfMonth();
        for ($i = 1; $i <= $mesesFuturos; $i++) {
            $periodo = $mesActual->copy()->addMonths($i)->format('Y-m');
            Cuota::factory()->create([
                'contrato_id' => $contrato->id,
                'periodo' => $periodo,
                'fecha_vencimiento' => $mesActual->copy()->addMonths($i)->setDay(5)->toDateString(),
                'monto_original' => $montoInicial,
                'saldo_pendiente' => $montoInicial,
                'estado' => 'PENDIENTE',
            ]);
        }

        return $contrato;
    }

    // =========================================================================
    // TEST DE PREVISUALIZACIÓN (POST /api/indexacion/previsualizar)
    // =========================================================================

    /** @test */
    public function test_previsualizar_ajuste_lista_contratos_correctos()
    {
        // Arrange: Crear 2 contratos activos que serán ajustados
        $contrato1 = $this->crearContratoConCuotas(6, 1000.00);
        $contrato2 = $this->crearContratoConCuotas(10, 2000.00);
        
        // Crear un contrato que NO debe ser listado (ej. finalizado)
        $contratoFinalizado = $this->crearContratoConCuotas(10, 1500.00);
        $contratoFinalizado->update(['estado' => ContratoEstado::FINALIZADO]);

        // Parámetros de previsualización (ajuste del 10% a partir del mes siguiente)
        $payload = [
            'tipoAjuste' => 'porcentaje',
            'valorAjuste' => 10,
            'fechaAplicacion' => Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d'),
        ];

        // Act
        $response = $this->actingAs($this->adminUser)->postJson('/api/indexacion/previsualizar', $payload);

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'contratos' => [
                    '*' => ['id', 'monto_alquiler', 'nuevo_monto_alquiler', 'cuotas_afectadas']
                ]
            ])
            // Solo deben aparecer los 2 contratos activos
            ->assertJsonCount(2, 'contratos')
            // Verificar que los ID de los contratos activos están presentes
            ->assertJsonFragment(['id' => $contrato1->id])
            ->assertJsonFragment(['id' => $contrato2->id])
            // Verificar que el contrato finalizado no está presente
            ->assertJsonMissing(['id' => $contratoFinalizado->id]);

        // Verificar el cálculo del nuevo monto para el contrato 1
        $response->assertJsonFragment([
            'id' => $contrato1->id,
            'monto_alquiler' => 1000.00,
            'nuevo_monto_alquiler' => 1100.00, // 1000 * 1.10
        ]);
    }

    // =========================================================================
    // TEST DE APLICACIÓN MASIVA (POST /api/indexacion/aplicar)
    // =========================================================================

    /** @test */
    public function test_aplicar_ajuste_actualiza_contratos_y_cuotas()
    {
        // Arrange
        $montoInicial = 1200.00;
        $mesesCuotas = 4;
        $contratoAjustar = $this->crearContratoConCuotas($mesesCuotas, $montoInicial);
        $fechaAplicacion = Carbon::now()->addMonth()->startOfMonth();
        $nuevoMontoEsperado = 1200.00 * (1 + 0.25); // 1500.00
        $diferencia = $nuevoMontoEsperado - $montoInicial; // 300.00

        // Payload para aplicar un 25% de aumento
        $payload = [
            'contratos_ids' => [$contratoAjustar->id],
            'tipoAjuste' => 'porcentaje',
            'valorAjuste' => 25,
            'fechaAplicacion' => $fechaAplicacion->format('Y-m-d'),
        ];

        // Act
        $response = $this->actingAs($this->adminUser)->postJson('/api/indexacion/aplicar', $payload);

        // Assert 1: Respuesta HTTP
        $response->assertOk()
                 ->assertJsonFragment(['total_ajustados' => 1])
                 ->assertJsonFragment(['message' => 'Ajuste masivo aplicado exitosamente a 1 contratos.']);

        // Assert 2: Contrato actualizado en la base de datos
        $this->assertDatabaseHas('contratos', [
            'id' => $contratoAjustar->id,
            'monto_alquiler' => $nuevoMontoEsperado,
            'estado' => ContratoEstado::ACTIVO,
        ]);

        // Assert 3: Cuotas futuras actualizadas
        // Contamos las cuotas afectadas que deberían ser las $mesesCuotas
        $cuotasActualizadas = Cuota::where('contrato_id', $contratoAjustar->id)
            ->where('periodo', '>=', $fechaAplicacion->format('Y-m'))
            ->get();
        
        $this->assertCount($mesesCuotas, $cuotasActualizadas);

        // Verificar que cada cuota afectada tenga el nuevo monto
        foreach ($cuotasActualizadas as $cuota) {
            $this->assertEquals($nuevoMontoEsperado, $cuota->monto_original);
            // El saldo pendiente debe ser igual al nuevo monto + la diferencia acumulada (si es que la cuota ya tenía otros saldos, 
            // aunque en este test es simple: nuevo monto base)
            $this->assertEquals($nuevoMontoEsperado, $cuota->saldo_pendiente); 
            // Se asume que el saldo pendiente inicial antes del ajuste es igual al importe inicial.
            // Si el importe inicial era 1200, y el saldo pendiente era 1200, la diferencia de 300 se suma al saldo: 1200 + 300 = 1500.
        }
    }
}