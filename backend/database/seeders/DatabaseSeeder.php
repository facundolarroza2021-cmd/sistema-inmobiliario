<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Propietario;
use App\Models\Propiedad;
use App\Models\Inquilino;
use App\Models\Contrato;
use App\Enums\ContratoEstado; // Asegúrate de importar tu Enum

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. EL ADMIN (Para que puedas loguearte en la demo)
        User::factory()->create([
            'name' => 'Admin Inmobiliaria',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'), // Contraseña fácil para la demo
        ]);

        // 2. ESCENARIO A: El Propietario "Estrella" (Para mostrar métricas altas)
        $propietarioTop = Propietario::factory()->create([
            'nombre_completo' => 'Juan Pérez (Dueño Top)',
        ]);

        // Le creamos 10 propiedades
        $propiedadesTop = Propiedad::factory(10)->create([
            'propietario_id' => $propietarioTop->id,
            'estado' => 'DISPONIBLE'
        ]);

        // Alquilamos 8 de ellas (80% de ocupación para que el gráfico se vea bonito)
        foreach($propiedadesTop->take(8) as $propiedad) {
            $this->crearContratoDemo($propiedad, 'ACTIVO');
        }

        // 3. ESCENARIO B: El Caso "Problemático" (Para mostrar alertas de deuda)
        $propiedadProblema = Propiedad::factory()->create([
            'titulo' => 'Casa con Deuda',
            'estado' => 'OCUPADO'
        ]);

        $this->crearContratoDemo($propiedadProblema, 'EN_MORA');

        // 4. RELLENO GENERAL (Para que las tablas tengan volumen)
        Propietario::factory(5)->create()->each(function ($prop) {
            Propiedad::factory(3)->create(['propietario_id' => $prop->id]);
        });
    }

    // Función auxiliar para crear contrato rápido
    private function crearContratoDemo($propiedad, $estadoEnum)
    {
        $inquilino = Inquilino::factory()->create();
        
        $contrato = Contrato::create([
            'inquilino_id' => $inquilino->id,
            'propiedad_id' => $propiedad->id,
            'monto_alquiler' => 120000,
            'fecha_inicio' => now()->subMonths(3), // Empezó hace 3 meses
            'fecha_fin' => now()->addMonths(9),
            'dia_vencimiento' => 5,
            'estado' => $estadoEnum == 'ACTIVO' ? \App\Enums\ContratoEstado::ACTIVO : \App\Enums\ContratoEstado::EN_MORA,
        ]);

        // Actualizamos estado propiedad
        $propiedad->update(['estado' => 'OCUPADO']);

        // Generamos cuotas ficticias (simulamos que pagó 2 y debe 1)
        // Aquí podrías llamar a tu CuotaService si quisieras ser estricto, 
        // pero para seeding rápido basta con crearlas si tienes la Factory de Cuotas.
    }
}