<?php
// src/Repository/AuditoriaRepository.php

namespace App\Repository;

use App\Entity\Auditoria;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Auditoria>
 */
class AuditoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auditoria::class);
    }

    /**
     * Busca acciones de auditoría por usuario
     */
    public function findByUsuario(Usuario $usuario, int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.usuario = :usuario')
            ->setParameter('usuario', $usuario)
            ->orderBy('a.creadoEn', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca acciones por tipo y entidad afectada
     */
    public function findByTipoAndEntidad(string $tipoAccion, string $entidadAfectada, int $entidadId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.tipoAccion = :tipo')
            ->andWhere('a.entidadAfectada = :entidad')
            ->andWhere('a.entidadId = :id')
            ->setParameter('tipo', $tipoAccion)
            ->setParameter('entidad', $entidadAfectada)
            ->setParameter('id', $entidadId)
            ->orderBy('a.creadoEn', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtiene últimas acciones para dashboard
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.usuario', 'u')
            ->addSelect('u')
            ->orderBy('a.creadoEn', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
