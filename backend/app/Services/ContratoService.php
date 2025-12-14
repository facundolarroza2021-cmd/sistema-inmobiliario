<?php

namespace App\Services;

use App\Models\Contrato;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\DTOs\ContratoData;
use App\Enums\ContratoEstado;

class ContratoService
{
    protected $cuotaService;

    public function __construct(CuotaService $cuotaService)
    {
        $this->cuotaService = $cuotaService;
    }

    public function crearContratoCompleto(ContratoData $datos, ?UploadedFile $archivo = null): Contrato
    {
        return DB::transaction(function () use ($datos, $archivo) {

            $rutaArchivo = $archivo ? $archivo->store('contratos', 'public') : null;

            $inicio = Carbon::parse($datos->fecha_inicio);
            $fin = $inicio->copy()->addMonths($datos->meses);

            // 1. Crear el Contrato usando el Enum
            $contrato = Contrato::create([
                'inquilino_id' => $datos->inquilino_id,
                'propiedad_id' => $datos->propiedad_id,
                'monto_alquiler' => $datos->monto_alquiler,
                'fecha_inicio' => $inicio->format('Y-m-d'),
                'fecha_fin' => $fin->format('Y-m-d'),
                'dia_vencimiento' => $datos->dia_vencimiento,
                'estado' => ContratoEstado::ACTIVO, 
                'archivo_url' => $rutaArchivo,
            ]);

            // 2. Crear Garantes (El DTO ya nos dio el array limpio)
            if (! empty($datos->garantes)) {
                foreach ($datos->garantes as $g) {
                    $contrato->garantes()->create([
                        'nombre_completo' => $g['nombre_completo'],
                        'dni' => $g['dni'],
                        'telefono' => $g['telefono'] ?? null,
                        'tipo_garantia' => $g['tipo'], // Asegúrate que el frontend envíe 'tipo'
                        'detalle_garantia' => $g['detalle'] ?? null,
                    ]);
                }
            }

            // 3. Generar Cuotas
            $this->cuotaService->generarCuotasParaContrato(
                $contrato,
                $datos->meses,
                $datos->dia_vencimiento
            );

            // 4. Actualizar estado de la propiedad (Opcional pero recomendado)
            $contrato->propiedad->update(['estado' => 'OCUPADO']);

            return $contrato;
        });
    }
    public function listarContratos()
    {
        return Contrato::with(['inquilino', 'propiedad', 'garantes'])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function finalizarContrato(int $id): void
    {
        $contrato = Contrato::findOrFail($id);
        
        // Usamos el Enum para finalizar
        $contrato->update([
            'estado' => ContratoEstado::FINALIZADO,
            // 'activo' => false // Descomentar si mantienes la columna legacy por seguridad
        ]);

        // Liberamos la propiedad
        $contrato->propiedad->update(['estado' => 'DISPONIBLE']);
    }
}

