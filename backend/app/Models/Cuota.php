<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    protected $guarded = [];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}