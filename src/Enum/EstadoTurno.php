<?php

namespace App\Enum;

enum EstadoTurno: string
{
    case RESERVADO = 'RESERVADO';
    case USADO = 'USADO';
    case CANCELADO = 'CANCELADO';
    case CADUCADO = 'CADUCADO';
    case EXPIRADO = 'EXPIRADO';

    public function getLabel(): string
    {
        return match($this) {
            self::RESERVADO => 'Reservado',
            self::USADO => 'Usado',
            self::CANCELADO => 'Cancelado',
            self::CADUCADO => 'Caducado',
            self::EXPIRADO => 'Expirado',
        };
    }
}
