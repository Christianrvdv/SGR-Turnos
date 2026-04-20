<?php
// src/Command/LoadRolesCommand.php

namespace App\Command;

use App\Entity\Rol;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    //php bin/console app:load-roles
    name: 'app:load-roles',
    description: 'Carga los roles predefinidos del sistema en la base de datos.',
)]
class LoadRolesCommand extends Command
{
    private const ROLES = [
        'ROLE_OPERADOR_BASICO' => 'Operador básico: acceso mínimo a turnos',
        'ROLE_OPERADOR'        => 'Operador: gestión completa de turnos',
        'ROLE_SUPERVISOR'      => 'Supervisor: supervisión y reportes',
        'ROLE_ADMIN'           => 'Administrador: configuración del sistema',
        'ROLE_SUPER_ADMIN'     => 'Super Administrador: acceso total sin restricciones',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Carga de roles del sistema');

        $rolRepository = $this->entityManager->getRepository(Rol::class);
        $created = 0;
        $existing = 0;

        foreach (self::ROLES as $nombre => $descripcion) {
            $rol = $rolRepository->findOneBy(['nombre' => $nombre]);
            if (!$rol) {
                $rol = new Rol();
                $rol->setNombre($nombre);
                $rol->setDescripcion($descripcion);
                $this->entityManager->persist($rol);
                $created++;
                $io->text("✓ Rol <info>$nombre</info> creado.");
            } else {
                $existing++;
                $io->text("○ Rol <info>$nombre</info> ya existe.");
            }
        }

        $this->entityManager->flush();

        $io->success("Carga completada: $created roles creados, $existing ya existían.");
        return Command::SUCCESS;
    }
}
