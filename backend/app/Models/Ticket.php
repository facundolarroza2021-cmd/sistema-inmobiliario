<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'propiedad_id',
        'inquilino_id',
        'titulo',
        'descripcion',
        'prioridad',
        'estado'
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function inquilino()
    {
        return $this->belongsTo(Inquilino::class);
    }
}