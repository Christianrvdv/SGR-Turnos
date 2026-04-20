<?php

namespace App\Service;

interface AuditoriaInterface
{
    public function registrar(
        string $tipoAccion,
        string $entidadAfectada,
        ?int   $entidadId = null,
        ?array $datosAntes = null,
        ?array $datosDespues = null
    ): void;
}
