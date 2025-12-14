<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Caja extends Model
{
    use HasFactory;

    // ESTO ES LO QUE FALTA:
    protected $fillable = [
        'tipo',         // INGRESO o EGRESO
        'concepto',     // Detalle del movimiento
        'monto',        // Cuanta plata
        'usuario_id',   // Quién lo hizo
        'fecha',        // Cuándo
        // 'referencia_id', 'referencia_type' // (Opcional si usas polimorfismo)
    ];
}
