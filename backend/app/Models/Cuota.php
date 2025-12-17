<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cuota extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }
    protected $fillable = [
        'contrato_id',
        'numero_cuota',
        'periodo',
        'fecha_vencimiento',
        'monto_original',
        'monto_original',
        'saldo_pendiente',
        'estado',
        'notas',
        'fecha_pago',
        'liquidacion_id'
    ];
    protected $casts = [
        'fecha_vencimiento' => 'datetime',
        'fecha_pago' => 'datetime',
    ];
}
