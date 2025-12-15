<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'propiedad_id', 
        'concepto',
        'monto',
        'fecha',
        'responsable',
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }
}
