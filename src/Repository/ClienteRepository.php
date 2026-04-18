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
}
