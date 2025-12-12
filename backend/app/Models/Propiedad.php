<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Propiedad extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'propiedades';

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class);
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    public function contratoActivo(): HasOne
    {
        return $this->hasOne(Contrato::class)->where('activo', true);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(PropiedadImagen::class);
    }

    public function gastos():HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function gastosPendientes(): HasMany
    {
        return $this->hasMany(Gasto::class)->whereNull('liquidacion_id');
    }
}
