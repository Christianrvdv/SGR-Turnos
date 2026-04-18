<?php

namespace App\Enum;

enum EstadoConfiguracion: string
{
    case ABIERTA = 'ABIERTA';
    case CERRADA = 'CERRADA';
    case FINALIZADA = 'FINALIZADA';

    public function getLabel(): string
    {
        return match($this) {
            self::ABIERTA => 'Abierta',
            self::CERRADA => 'Cerrada',
            self::FINALIZADA => 'Finalizada',
        };
    }
}
