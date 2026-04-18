<?php
// src/Repository/ConfiguracionDiariaRepository.php

namespace App\Repository;

use App\Entity\ConfiguracionDiaria;
use App\Entity\Servicio;
use App\Enum\EstadoConfiguracion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConfiguracionDiaria>
 */
class ConfiguracionDiariaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfiguracionDiaria::class);
    }

    /**
     * Busca la configuración activa para un servicio y fecha
     */
    public function findActivaByServicioYFecha(Servicio $servicio, \DateTimeInterface $fecha): ?ConfiguracionDiaria
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.servicio = :servicio')
            ->andWhere('c.fecha = :fecha')
            ->andWhere('c.estado = :estado')
            ->setParameter('servicio', $servicio)
            ->setParameter('fecha', $fecha)
            ->setParameter('estado', EstadoConfiguracion::ABIERTA)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obtiene configuraciones por rango de fechas
     */
    public function findByServicioAndRangoFechas(Servicio $servicio, \DateTimeInterface $inicio, \DateTimeInterface $fin): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.servicio = :servicio')
            ->andWhere('c.fecha BETWEEN :inicio AND :fin')
            ->setParameter('servicio', $servicio)
            ->setParameter('inicio', $inicio)
            ->setParameter('fin', $fin)
            ->orderBy('c.fecha', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Verifica si existe configuración para una fecha (sin importar estado)
     */
    public function existsByServicioYFecha(Servicio $servicio, \DateTimeInterface $fecha): bool
    {
        $result = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.servicio = :servicio')
            ->andWhere('c.fecha = :fecha')
            ->setParameter('servicio', $servicio)
            ->setParameter('fecha', $fecha)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }
}
