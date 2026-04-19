<?php
// src/Service/DashboardService.php

namespace App\Service;

use App\Entity\Servicio;
use App\Repository\ConfiguracionDiariaRepository;
use App\Repository\TurnoRepository;
use App\Repository\ServicioRepository;

class DashboardService implements DashboardInterface
{
    public function __construct(
        private readonly ConfiguracionDiariaRepository $configuracionDiariaRepository,
        private readonly TurnoRepository               $turnoRepository,
        private readonly ServicioRepository            $servicioRepository,
    )
    {
    }

    /**
     * Obtiene todos los datos necesarios para el dashboard principal.
     */
    public function getDashboardData(\DateTimeInterface $fecha = null): array
    {
        $fecha = $fecha ?? new \DateTime('today');

        // Obtener todos los servicios activos (para sumar turnos disponibles globales)
        $serviciosActivos = $this->servicioRepository->findActivos();

        // Calcular total de turnos pendientes para hoy (suma de todos los servicios)
        $totalPendientesHoy = 0;
        $totalTicketsDisponibles = 0;

        foreach ($serviciosActivos as $servicio) {
            $configuracionActiva = $this->configuracionDiariaRepository->findActivaByServicioYFecha($servicio, $fecha);
            if ($configuracionActiva) {
                $totalTicketsDisponibles += $configuracionActiva->getTicketsRestantes();
            }

            // Contar turnos reservados (pendientes) para hoy en este servicio
            $pendientesServicio = $this->turnoRepository->countReservadosByServicioYFecha($servicio, $fecha);
            $totalPendientesHoy += $pendientesServicio;
        }

        // Obtener lista de servicios con detalles (para posibles widgets adicionales)
        $serviciosConDetalles = [];
        foreach ($serviciosActivos as $servicio) {
            $serviciosConDetalles[] = [
                'id' => $servicio->getId(),
                'nombre' => $servicio->getNombre(),
                'codigo' => $servicio->getCodigo(),
                'ticketsRestantes' => $this->getTicketsRestantesParaServicio($servicio, $fecha),
                'turnosPendientes' => $this->turnoRepository->countReservadosByServicioYFecha($servicio, $fecha),
            ];
        }

        return [
            'fecha' => $fecha,
            'totalPendientesHoy' => $totalPendientesHoy,
            'totalTicketsDisponibles' => $totalTicketsDisponibles,
            'servicios' => $serviciosConDetalles,
            'semana' => (int)$fecha->format('W'),
            'diaSemana' => $this->getDiaSemanaEnEspanol($fecha),
        ];
    }

    private function getTicketsRestantesParaServicio(Servicio $servicio, \DateTimeInterface $fecha): int
    {
        $config = $this->configuracionDiariaRepository->findActivaByServicioYFecha($servicio, $fecha);
        return $config ? $config->getTicketsRestantes() : 0;
    }

    private function getDiaSemanaEnEspanol(\DateTimeInterface $fecha): string
    {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return $dias[(int)$fecha->format('w')];
    }
}
