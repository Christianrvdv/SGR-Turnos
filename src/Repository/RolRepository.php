<?php
// src/Repository/RolRepository.php

namespace App\Repository;

use App\Entity\Rol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rol>
 */
class RolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rol::class);
    }

    /**
     * Busca un rol por su nombre (ej: 'ROLE_SUPER_ADMIN')
     */
    public function findOneByNombre(string $nombre): ?Rol
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.nombre = :nombre')
            ->setParameter('nombre', $nombre)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
