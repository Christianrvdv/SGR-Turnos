<?php
// src/Controller/Admin/ConfiguracionDiariaController.php

namespace App\Controller\Admin;

use App\Repository\ConfiguracionDiariaRepository;
use App\Repository\ServicioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ConfiguracionDiariaController extends AbstractController
{
    public function __construct(
        private readonly ConfiguracionDiariaRepository $configuracionDiariaRepository,
        private readonly ServicioRepository $servicioRepository,
    ) {
    }

    #[Route('/admin/calendario', name: 'admin_calendario_index')]
    public function index(Request $request): Response
    {
        // Obtener mes y año actual o de la solicitud
        $now = new \DateTime();
        $mes = $request->query->getInt('mes', (int) $now->format('m'));
        $anio = $request->query->getInt('anio', (int) $now->format('Y'));

        $fechaInicio = new \DateTime("{$anio}-{$mes}-01");
        $fechaFin = clone $fechaInicio;
        $fechaFin->modify('last day of this month');

        // Obtener servicios activos para el selector (si se necesita filtrar por servicio)
        $servicios = $this->servicioRepository->findActivos();
        $servicioId = $request->query->get('servicio');
        $servicioSeleccionado = $servicioId ? $this->servicioRepository->find($servicioId) : ($servicios[0] ?? null);

        // Obtener configuraciones del mes
        $configuraciones = [];
        if ($servicioSeleccionado) {
            $configuraciones = $this->configuracionDiariaRepository->findByServicioAndRangoFechas(
                $servicioSeleccionado,
                $fechaInicio,
                $fechaFin
            );
        }

        // Construir datos del calendario
        $diasDelMes = [];
        $primerDia = (int) $fechaInicio->format('N'); // 1 (lunes) a 7 (domingo)
        $totalDias = (int) $fechaFin->format('d');

        // Mapa de configuraciones por día
        $configPorDia = [];
        foreach ($configuraciones as $config) {
            $dia = (int) $config->getFecha()->format('d');
            $configPorDia[$dia] = $config;
        }

        // Llenar días previos (espacios vacíos)
        for ($i = 1; $i < $primerDia; $i++) {
            $diasDelMes[] = null;
        }

        // Llenar días del mes
        for ($dia = 1; $dia <= $totalDias; $dia++) {
            $fechaDia = new \DateTime("{$anio}-{$mes}-{$dia}");
            $config = $configPorDia[$dia] ?? null;
            $diasDelMes[] = [
                'dia' => $dia,
                'fecha' => $fechaDia,
                'config' => $config,
                'esHoy' => $fechaDia->format('Y-m-d') === $now->format('Y-m-d'),
            ];
        }

        // Estadísticas rápidas (últimos 7 días)
        $stats = $this->getEstadisticasRapidas($servicioSeleccionado);

        return $this->render('admin/calendario/index.html.twig', [
            'servicios' => $servicios,
            'servicioSeleccionado' => $servicioSeleccionado,
            'diasDelMes' => $diasDelMes,
            'mesActual' => $mes,
            'anioActual' => $anio,
            'nombreMes' => $this->getNombreMes($mes),
            'mesAnterior' => $mes === 1 ? 12 : $mes - 1,
            'anioAnterior' => $mes === 1 ? $anio - 1 : $anio,
            'mesSiguiente' => $mes === 12 ? 1 : $mes + 1,
            'anioSiguiente' => $mes === 12 ? $anio + 1 : $anio,
            'stats' => $stats,
        ]);
    }

    private function getNombreMes(int $mes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$mes] ?? '';
    }

    private function getEstadisticasRapidas(?object $servicio): array
    {
        if (!$servicio) {
            return ['promedioTickets' => 0, 'totalConfiguraciones' => 0];
        }

        $hace7Dias = new \DateTime('-7 days');
        $hoy = new \DateTime();
        $configs = $this->configuracionDiariaRepository->findByServicioAndRangoFechas($servicio, $hace7Dias, $hoy);

        $totalTickets = 0;
        foreach ($configs as $c) {
            $totalTickets += $c->getTicketsGenerados();
        }

        return [
            'promedioTickets' => count($configs) > 0 ? round($totalTickets / count($configs)) : 0,
            'totalConfiguraciones' => count($configs),
        ];
    }
}
