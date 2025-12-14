<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Propietario extends Model
{
    protected $guarded = [];
    use HasFactory;

    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class);
    }

    public function liquidaciones(): HasMany
    {
        return $this->hasMany(Liquidacion::class);
    }
}
