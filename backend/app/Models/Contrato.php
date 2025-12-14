<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ContratoEstado;

class Contrato extends Model
{
    protected $guarded = [];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => ContratoEstado::class, 
    ];
    public function inquilino(): BelongsTo
    {
        return $this->belongsTo(Inquilino::class);
    }

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function cuotas() : HasMany
    {
        return $this->hasMany(Cuota::class);
    }

    public function garantes(): HasMany
    {
        return $this->hasMany(Garante::class);
    }
}
