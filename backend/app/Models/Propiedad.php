<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Propiedad extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    
    protected $table = 'propiedades';

    public function propietario()
    {
        return $this->belongsTo(Propietario::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }
    public function contratoActivo()
    {
        return $this->hasOne(Contrato::class)->where('activo', true);
    }
    public function imagenes()
    {
        return $this->hasMany(PropiedadImagen::class);
    }
    public function gastos()
    {
        return $this->hasMany(Gasto::class);
    }
    public function gastosPendientes()
    {
        return $this->hasMany(Gasto::class)->whereNull('liquidacion_id');
    }
}