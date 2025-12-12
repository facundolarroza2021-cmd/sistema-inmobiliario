<?php

namespace App\Http\Controllers;

use App\Services\PropiedadService;
use Illuminate\Http\Request;

class PropiedadController extends Controller
{
    protected $propiedadService;

    public function __construct(PropiedadService $propiedadService)
    {
        $this->propiedadService = $propiedadService;
    }

    /**
     * @OA\Get(
     * path="/api/propiedades",
     * summary="Listar todas las propiedades",
     * tags={"Propiedades"},
     * security={{"sanctum":{}}},
     *
     * @OA\Response(
     * response=200,
     * description="Lista de propiedades recuperada con éxito",
     *
     * @OA\JsonContent(
     * type="array",
     *
     * @OA\Items(
     *
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="direccion", type="string", example="Av. Siempre Viva 742"),
     * @OA\Property(property="tipo", type="string", example="Casa"),
     * @OA\Property(property="estado", type="string", example="Disponible"),
     * @OA\Property(property="precio_alquiler", type="number", example=150000),
     * @OA\Property(property="propietario", type="object", description="Datos del dueño")
     * )
     * )
     * )
     * )
     */
    public function index()
    {
        return response()->json($this->propiedadService->listarTodo());
    }

    /**
     * @OA\Get(
     * path="/api/propiedades/{id}",
     * summary="Obtener detalles de una propiedad",
     * tags={"Propiedades"},
     * security={{"sanctum":{}}},
     *
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     * @OA\Response(response=200, description="Detalles de la propiedad"),
     * @OA\Response(response=404, description="Propiedad no encontrada")
     * )
     */
    public function show($id)
    {
        return response()->json($this->propiedadService->obtenerDetalle($id));
    }

    /**
     * @OA\Post(
     * path="/api/propiedades",
     * summary="Crear una nueva propiedad",
     * tags={"Propiedades"},
     * security={{"sanctum":{}}},
     *
     * @OA\RequestBody(
     * required=true,
     *
     * @OA\JsonContent(
     * required={"propietario_id", "direccion", "tipo"},
     *
     * @OA\Property(property="propietario_id", type="integer", example=1, description="ID del dueño existente"),
     * @OA\Property(property="direccion", type="string", example="Av. Siempre Viva 742"),
     * @OA\Property(property="tipo", type="string", example="Departamento"),
     * @OA\Property(property="ambientes", type="integer", example=3),
     * @OA\Property(property="precio_alquiler", type="number", example=null, description="Opcional"),
     * @OA\Property(property="comision", type="number", example=null, description="Opcional")
     * )
     * ),
     *
     * @OA\Response(
     * response=201,
     * description="Propiedad creada con éxito",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="message", type="string", example="Propiedad creada"),
     * @OA\Property(property="data", type="object")
     * )
     * )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'propietario_id' => 'required|exists:propietarios,id',
            'direccion' => 'required|string',
            'tipo' => 'required|string',
            'precio_alquiler' => 'nullable|numeric',
            'comision' => 'nullable|numeric',
        ]);

        $propiedad = $this->propiedadService->crearPropiedad($validated);

        return response()->json($propiedad, 201);
    }

    /**
     * @OA\Post(
     * path="/api/propiedades/{id}/fotos",
     * summary="Subir una foto a la propiedad",
     * tags={"Propiedades"},
     * security={{"sanctum":{}}},
     *
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID de la propiedad",
     * required=true,
     *
     * @OA\Schema(type="integer")
     * ),
     *
     * @OA\RequestBody(
     * required=true,
     * description="Archivo de imagen",
     *
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     *
     * @OA\Schema(
     *
     * @OA\Property(
     * property="imagen",
     * description="Archivo de imagen (jpg, png, jpeg)",
     * type="string",
     * format="binary"
     * )
     * )
     * )
     * ),
     *
     * @OA\Response(
     * response=200,
     * description="Foto subida con éxito",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="mensaje", type="string", example="Foto subida"),
     * @OA\Property(property="url", type="string", example="http://localhost:8000/storage/propiedades/1/foto.jpg")
     * )
     * ),
     *
     * @OA\Response(response=422, description="Error de validación (archivo muy grande o formato inválido)")
     * )
     */
    public function uploadFoto(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|max:5120',
        ]);

        $resultado = $this->propiedadService->subirFoto($id, $request->file('imagen'));

        return response()->json(['mensaje' => 'Foto subida', ...$resultado]);
    }

    /**
     * @OA\Delete(
     * path="/api/propiedades/{id}",
     * summary="Eliminar una propiedad",
     * tags={"Propiedades"},
     * security={{"sanctum":{}}},
     *
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID de la propiedad a eliminar",
     * required=true,
     *
     * @OA\Schema(type="integer")
     * ),
     *
     * @OA\Response(
     * response=200,
     * description="Propiedad eliminada correctamente",
     *
     * @OA\JsonContent(
     *
     * @OA\Property(property="message", type="string", example="Propiedad eliminada")
     * )
     * ),
     *
     * @OA\Response(response=404, description="Propiedad no encontrada")
     * )
     */
    public function destroy($id)
    {
        $this->propiedadService->eliminarPropiedad($id);

        return response()->json(['message' => 'Propiedad eliminada']);
    }
}
