<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garante extends Model
{
    use HasFactory;

    protected $guarded = []; // Permite asignación masiva

    // Relación inversa (opcional, pero útil)
    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }
}