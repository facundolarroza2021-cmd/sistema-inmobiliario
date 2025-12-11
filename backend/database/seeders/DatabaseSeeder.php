<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Propietario;
use App\Models\Inquilino;
use App\Models\Propiedad;
use App\Models\Contrato;
use App\Models\Cuota; // <--- Importante
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'activo' => true,
            'password' => 'password123' 
        ]);
        // 1. PROPIETARIOS
        $prop1 = Propietario::create([
            'nombre_completo' => 'Roberto Dueñas (VIP)',
            'dni' => '20.123.456',
            'email' => 'roberto@email.com',
            'telefono' => '11-5555-0001',
            'cbu' => '0000003100011122233344'
        ]);

        $prop2 = Propietario::create([
            'nombre_completo' => 'Laura Propietaria',
            'dni' => '27.987.654',
            'email' => 'laura@email.com',
            'telefono' => '11-5555-0002'
        ]);

        // 2. PROPIEDADES
        $casa1 = Propiedad::create([
            'direccion' => 'Av. San Martín 450',
            'tipo' => 'Casa',
            'propietario_id' => $prop1->id,
            'comision' => 10
        ]);

        $depto1 = Propiedad::create([
            'direccion' => 'Mitre 1200, Piso 4 A',
            'tipo' => 'Departamento',
            'propietario_id' => $prop1->id,
            'comision' => 8
        ]);

        $local1 = Propiedad::create([
            'direccion' => 'Peatonal 850 (Local)',
            'tipo' => 'Local',
            'propietario_id' => $prop2->id,
            'comision' => 15
        ]);

        // 3. INQUILINOS
        $inq1 = Inquilino::create([
            'nombre_completo' => 'Juan Pagador (Al día)',
            'dni' => '30.111.222',
            'email' => 'juan@gmail.com',
            'telefono' => '11-4444-5555'
        ]);

        $inq2 = Inquilino::create([
            'nombre_completo' => 'Lucía Nueva',
            'dni' => '31.333.444',
            'email' => 'lucia@gmail.com',
            'telefono' => '11-4444-6666'
        ]);

        $inq3 = Inquilino::create([
            'nombre_completo' => 'Pedro Deudor',
            'dni' => '32.555.666',
            'email' => 'pedro@gmail.com',
            'telefono' => '11-4444-7777'
        ]);

        // 4. CONTRATOS Y GENERACIÓN DE CUOTAS
        
        // CASO A: Contrato Normal (Juan)
        $con1 = Contrato::create([
            'propiedad_id' => $casa1->id,
            'inquilino_id' => $inq1->id,
            'fecha_inicio' => Carbon::now()->subMonths(6),
            'fecha_fin' => Carbon::now()->addMonths(18),
            'monto_alquiler' => 350000,
            'dia_vencimiento' => 10,
            'activo' => true
        ]);
        $this->generarCuotas($con1); // <--- Generamos la deuda
        
        // Simulamos que Juan pagó las primeras 5
        foreach($con1->cuotas as $cuota) {
            if ($cuota->periodo < Carbon::now()->format('Y-m')) {
                $cuota->update(['estado' => 'PAGADO', 'saldo_pendiente' => 0]);
            }
        }

        // CASO B: Contrato Nuevo (Lucía)
        $con2 = Contrato::create([
            'propiedad_id' => $depto1->id,
            'inquilino_id' => $inq2->id,
            'fecha_inicio' => Carbon::now()->startOfMonth(),
            'fecha_fin' => Carbon::now()->addMonths(24),
            'monto_alquiler' => 280000,
            'dia_vencimiento' => 5,
            'activo' => true
        ]);
        $this->generarCuotas($con2); // <--- Generamos la deuda (aparecerá en caja)

        // CASO C: Contrato Deudor (Pedro)
        $con3 = Contrato::create([
            'propiedad_id' => $local1->id,
            'inquilino_id' => $inq3->id,
            'fecha_inicio' => Carbon::now()->subMonths(10),
            'fecha_fin' => Carbon::now()->addMonths(14),
            'monto_alquiler' => 500000,
            'dia_vencimiento' => 1,
            'activo' => true
        ]);
        $this->generarCuotas($con3);
    }

    // --- FUNCIÓN AUXILIAR PARA GENERAR CUOTAS ---
    private function generarCuotas($contrato)
    {
        $inicio = Carbon::parse($contrato->fecha_inicio);
        $fin = Carbon::parse($contrato->fecha_fin);
        $fecha_aux = $inicio->copy();
        $numero_cuota = 1;

        while ($fecha_aux->lt($fin)) {
            $vencimiento = $fecha_aux->copy()->day($contrato->dia_vencimiento);
            
            Cuota::create([
                'contrato_id' => $contrato->id,
                'numero_cuota' => $numero_cuota,
                'periodo' => $fecha_aux->format('Y-m'),
                'fecha_vencimiento' => $vencimiento->format('Y-m-d'),
                'monto_original' => $contrato->monto_alquiler,
                'saldo_pendiente' => $contrato->monto_alquiler,
                'estado' => 'PENDIENTE'
            ]);

            $fecha_aux->addMonth();
            $numero_cuota++;
        }
    }
}