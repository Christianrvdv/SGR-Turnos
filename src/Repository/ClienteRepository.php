<?php
// src/Repository/ClienteRepository.php

namespace App\Repository;

use App\Entity\Cliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cliente>
 */
class ClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }

    /**
     * Busca o crea un cliente por su número de identidad
     */
    public function findOrCreateByIdentidad(string $numeroIdentidad): Cliente
    {
        $cliente = $this->findOneBy(['numeroIdentidad' => $numeroIdentidad]);

        if (!$cliente) {
            $cliente = new Cliente();
            $cliente->setNumeroIdentidad($numeroIdentidad);
            $this->getEntityManager()->persist($cliente);
            // No hacemos flush aquí, se hará cuando se asigne el turno
        }

        return $cliente;
    }

    /**
     * Busca cliente por identidad exacta
     */
    public function findByIdentidad(string $numeroIdentidad): ?Cliente
    {
        return $this->findOneBy(['numeroIdentidad' => $numeroIdentidad]);
    }

    /**
     * Busca o crea un cliente por su número de tarjeta
     */
    public function findOrCreateByTarjeta(string $numeroTarjeta): Cliente
    {
        $cliente = $this->findOneBy(['numeroTarjeta' => $numeroTarjeta]);

        if (!$cliente) {
            $cliente = new Cliente();
            $cliente->setNumeroTarjeta($numeroTarjeta);
            // Opcional: marcar que falta el número de identidad
            $this->getEntityManager()->persist($cliente);
        }

        return $cliente;
    }

    /**
     * Busca cliente por tarjeta exacta
     */
    public function findByTarjeta(string $numeroTarjeta): ?Cliente
    {
        return $this->findOneBy(['numeroTarjeta' => $numeroTarjeta]);
    }

    /**
     * Busca cliente por identidad o tarjeta (útil para un único campo de búsqueda genérico)
     */
    public function findByIdentidadOrTarjeta(string $identificador): ?Cliente
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.numeroIdentidad = :id OR c.numeroTarjeta = :id')
            ->setParameter('id', $identificador)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
