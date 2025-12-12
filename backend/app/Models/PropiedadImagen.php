<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $propiedad_id
 * @property string $ruta_archivo
 * @property string|null $url 
 */
class PropiedadImagen extends Model
{
    use HasFactory;

    protected $table = 'propiedad_imagenes';

    protected $guarded = [];
}
