<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clase que representa un registro individual de pago aplicado a una cuota.
 * Es la entidad de registro financiero principal para la entrada de caja.
 */
class Pago extends Model
{
    /**
     * @var string[] Los campos que se pueden asignar masivamente (Mass Assignment).
     * Incluye datos financieros, de identificación y de seguimiento.
     */
    protected $fillable = [
        'cuota_id',          
        'monto_pagado',      
        'fecha_pago',        
        'forma_pago',        
        'nro_comprobante',   
        'observacion',       
        'ruta_pdf',          
    ];

    /**
     * @var string[] Conversión automática de campos a tipos nativos de PHP.
     */
    protected $casts = [
        'fecha_pago' => 'datetime', // Convierte el campo a objeto Carbon.
    ];

    /**
     * Define la relación inversa: Un Pago pertenece a una única Cuota.
     * Es crucial para cargar el contexto (Contrato, Inquilino, Propiedad) del pago.
     *
     * @return BelongsTo
     */
    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class);
    }
}