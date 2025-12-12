<?php

namespace App\Services;

use App\Models\Contrato;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ContratoService
{
    protected $cuotaService;

    public function __construct(CuotaService $cuotaService)
    {
        $this->cuotaService = $cuotaService;
    }

    public function crearContratoCompleto(array $datos, ?UploadedFile $archivo = null): Contrato
    {
        return DB::transaction(function () use ($datos, $archivo) {

            $rutaArchivo = $archivo ? $archivo->store('contratos', 'public') : null;

            $inicio = Carbon::parse($datos['fecha_inicio']);
            $meses = (int) ($datos['meses'] ?? 12);
            $fin = $inicio->copy()->addMonths($meses);

            $contrato = Contrato::create([
                'inquilino_id' => $datos['inquilino_id'],
                'propiedad_id' => $datos['propiedad_id'],
                'monto_alquiler' => $datos['monto_actual'],
                'fecha_inicio' => $inicio->format('Y-m-d'),
                'fecha_fin' => $fin->format('Y-m-d'),
                'dia_vencimiento' => (int) $datos['dia_vencimiento'],
                'activo' => true,
                'archivo_url' => $rutaArchivo,
            ]);

            if (! empty($datos['garantes'])) {
                $garantes = is_string($datos['garantes']) ? json_decode($datos['garantes'], true) : $datos['garantes'];
                foreach ($garantes as $g) {
                    $contrato->garantes()->create([
                        'nombre_completo' => $g['nombre_completo'],
                        'dni' => $g['dni'],
                        'telefono' => $g['telefono'] ?? null,
                        'tipo_garantia' => $g['tipo'],
                        'detalle_garantia' => $g['detalle'] ?? null,
                    ]);
                }
            }

            $this->cuotaService->generarCuotasParaContrato(
                $contrato,
                $meses,
                (int) $datos['dia_vencimiento']
            );

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
        $contrato->update(['activo' => false]);
    }
}
