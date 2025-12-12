<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="API Sistema Inmobiliario",
 * description="Documentación de la API para gestión de propiedades, inquilinos y contratos.",
 *
 * @OA\Contact(
 * email="admin@test.com"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="API Server"
 * )
 *
 * * @OA\SecurityScheme(
 * type="http",
 * description="Login con token Bearer",
 * name="Token de Acceso",
 * in="header",
 * scheme="bearer",
 * bearerFormat="JWT",
 * securityScheme="sanctum",
 * )
 */
abstract class Controller
{
    //
}
