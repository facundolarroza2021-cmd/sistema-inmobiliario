<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    protected $guarded = [];

    public function propiedades()
    {
        return $this->hasMany(Propiedad::class);
    }
    
    public function liquidaciones()
    {
        return $this->hasMany(Liquidacion::class);
    }
}