<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $guarded = [];

    public function inquilino()
    {
        return $this->belongsTo(Inquilino::class);
    }

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }

    public function garantes()
    {
        return $this->hasMany(Garante::class);
    }
}