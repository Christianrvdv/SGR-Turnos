<?php
// src/Repository/ServicioRepository.php

namespace App\Repository;

use App\Entity\Servicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Servicio>
 */
class ServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Servicio::class);
    }

    /**
     * Obtiene todos los servicios activos
     */
    public function findActivos(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.activo = :activo')
            ->setParameter('activo', true)
            ->orderBy('s.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca servicio por código único
     */
    public function findByCodigo(string $codigo): ?Servicio
    {
        return $this->findOneBy(['codigo' => $codigo]);
    }
}
