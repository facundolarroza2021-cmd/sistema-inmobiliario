<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liquidacion extends Model
{

    protected $table = 'liquidaciones';
    
    protected $guarded = [];
    protected $casts = [
        'fecha' => 'date',
    ];

    public function propietario()
    {
        return $this->belongsTo(Propietario::class);
    }
    public function gastos()
    {
        return $this->hasMany(Gasto::class);
    }
}