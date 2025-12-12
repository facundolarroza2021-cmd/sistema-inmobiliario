<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $guarded = [];

    // Convierte esto automáticamente a objeto fecha (Carbon)
    protected $casts = [
        'fecha_pago' => 'datetime',
    ];

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class);
    }
}
