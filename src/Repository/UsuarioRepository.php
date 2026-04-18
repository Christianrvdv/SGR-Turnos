<?php
// src/Repository/UsuarioRepository.php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Usuario>
 */
class UsuarioRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * Usado por Symfony Security para actualizar contraseñas
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Usuario) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setContrasenaHash($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Busca usuarios activos por rol
     */
    public function findActiveByRol(string $rolNombre): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.rol', 'r')
            ->andWhere('r.nombre = :rol')
            ->andWhere('u.activo = :activo')
            ->setParameter('rol', $rolNombre)
            ->setParameter('activo', true)
            ->orderBy('u.nombreCompleto', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
