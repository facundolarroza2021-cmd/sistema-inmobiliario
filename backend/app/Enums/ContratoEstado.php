<?php

namespace App\Enums;

enum ContratoEstado: string
{
    case ACTIVO = 'ACTIVO';
    case FINALIZADO = 'FINALIZADO';
    case RESCINDIDO = 'RESCINDIDO';
    case EN_MORA = 'EN_MORA';
    case ACTIVO_SPANISH = 'activo'; 

    public function label(): string
    {
        return match($this) {
            self::ACTIVO => 'Vigente',
            self::FINALIZADO => 'Cumplido',
            self::RESCINDIDO => 'Cancelado antes de tiempo',
            self::EN_MORA => 'Con deuda pendiente',
            self::ACTIVO_SPANISH => 'Activo', 
        };
    }
}