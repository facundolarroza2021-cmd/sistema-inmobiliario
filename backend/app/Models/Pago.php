<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $guarded = [];

    // Convierte esto automáticamente a objeto fecha (Carbon)
    protected $casts = [
        'fecha_pago' => 'datetime',
    ];

    public function cuota()
    {
        return $this->belongsTo(Cuota::class);
    }
}