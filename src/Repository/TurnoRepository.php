<?php
// src/Repository/TurnoRepository.php

namespace App\Repository;

use App\Entity\Cliente;
use App\Entity\ConfiguracionDiaria;
use App\Entity\Servicio;
use App\Entity\Turno;
use App\Enum\EstadoTurno;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Turno>
 */
class TurnoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Turno::class);
    }

    /**
     * Obtiene la fecha del último turno USADO de un cliente para un servicio específico
     */
    public function getFechaUltimoRetiro(Cliente $cliente, Servicio $servicio): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.fechaUso')
            ->andWhere('t.cliente = :cliente')
            ->andWhere('t.servicio = :servicio')
            ->andWhere('t.estado = :estado')
            ->setParameter('cliente', $cliente)
            ->setParameter('servicio', $servicio)
            ->setParameter('estado', EstadoTurno::USADO)
            ->orderBy('t.fechaUso', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['fechaUso'] : null;
    }

    /**
     * Busca turnos por configuración diaria (para listado impreso)
     */
    public function findByConfiguracionDiaria(ConfiguracionDiaria $configuracion): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.configuracionDiaria = :config')
            ->setParameter('config', $configuracion)
            ->orderBy('t.numeroTurno', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca turnos pendientes para una fecha y servicio
     */
    public function findPendientesByServicioYFecha(Servicio $servicio, \DateTimeInterface $fecha): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.configuracionDiaria', 'c')
            ->andWhere('t.servicio = :servicio')
            ->andWhere('c.fecha = :fecha')
            ->andWhere('t.estado = :estado')
            ->setParameter('servicio', $servicio)
            ->setParameter('fecha', $fecha)
            ->setParameter('estado', EstadoTurno::RESERVADO)
            ->orderBy('t.numeroTurno', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Cuenta turnos asignados para un día y servicio (para validar disponibilidad)
     */
    public function countReservadosByServicioYFecha(Servicio $servicio, \DateTimeInterface $fecha): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->join('t.configuracionDiaria', 'c')
            ->andWhere('t.servicio = :servicio')
            ->andWhere('c.fecha = :fecha')
            ->andWhere('t.estado = :estado')
            ->setParameter('servicio', $servicio)
            ->setParameter('fecha', $fecha)
            ->setParameter('estado', EstadoTurno::RESERVADO)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Obtiene el siguiente número de turno disponible para una configuración diaria
     */
    public function getNextNumeroTurno(ConfiguracionDiaria $configuracion): string
    {
        $ultimo = $this->createQueryBuilder('t')
            ->select('t.numeroTurno')
            ->andWhere('t.configuracionDiaria = :config')
            ->setParameter('config', $configuracion)
            ->orderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$ultimo) {
            return '001';
        }

        $numero = (int) $ultimo['numeroTurno'];
        return str_pad((string) ($numero + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica si un cliente ya tiene un turno en una configuración diaria específica
     */
    public function existsTurnoForClienteInConfiguracion(Cliente $cliente, ConfiguracionDiaria $configuracion): bool
    {
        $result = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.cliente = :cliente')
            ->andWhere('t.configuracionDiaria = :config')
            ->setParameter('cliente', $cliente)
            ->setParameter('config', $configuracion)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }
}
