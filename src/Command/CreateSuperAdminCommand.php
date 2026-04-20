<?php
// src/Command/CreateSuperAdminCommand.php

namespace App\Command;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Repository\RolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-super-admin',
    description: 'Crea un nuevo usuario Super Admin en el sistema.',
)]
class CreateSuperAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RolRepository               $rolRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'Nombre de usuario')
            ->addOption('email', 'm', InputOption::VALUE_REQUIRED, 'Correo electrónico')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Contraseña (mínimo 8 caracteres)')
            ->addOption('fullname', 'f', InputOption::VALUE_REQUIRED, 'Nombre completo')
            ->addOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Ejecutar sin preguntas interactivas');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creación de Usuario Super Admin');

        // Obtener o crear el rol ROLE_SUPER_ADMIN
        $rolSuperAdmin = $this->rolRepository->findOneByNombre('ROLE_SUPER_ADMIN');

        if (!$rolSuperAdmin) {
            $io->text('El rol ROLE_SUPER_ADMIN no existe. Creándolo automáticamente...');
            $rolSuperAdmin = new Rol();
            $rolSuperAdmin->setNombre('ROLE_SUPER_ADMIN');
            $rolSuperAdmin->setDescripcion('Acceso total al sistema sin restricciones');
            $this->entityManager->persist($rolSuperAdmin);
            $this->entityManager->flush();
            $io->success('Rol ROLE_SUPER_ADMIN creado correctamente.');
        }

        // Verificar si se pasaron opciones por línea de comandos
        $username = $input->getOption('username');
        $email = $input->getOption('email');
        $password = $input->getOption('password');
        $fullname = $input->getOption('fullname');

        // Modo interactivo si faltan datos y no se usa --no-interaction
        $helper = $this->getHelper('question');
        $interactive = !$input->getOption('no-interaction');

        if ($interactive) {
            if (!$username) {
                $question = new Question('Nombre de usuario: ');
                $question->setValidator(function ($answer) {
                    if (empty($answer)) {
                        throw new \RuntimeException('El nombre de usuario no puede estar vacío.');
                    }
                    return $answer;
                });
                $username = $helper->ask($input, $output, $question);
            }

            if (!$email) {
                $question = new Question('Correo electrónico: ');
                $question->setValidator(function ($answer) {
                    if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                        throw new \RuntimeException('Debe proporcionar un correo electrónico válido.');
                    }
                    return $answer;
                });
                $email = $helper->ask($input, $output, $question);
            }

            if (!$password) {
                $question = new Question('Contraseña (mínimo 8 caracteres): ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                $question->setValidator(function ($answer) {
                    if (strlen($answer) < 8) {
                        throw new \RuntimeException('La contraseña debe tener al menos 8 caracteres.');
                    }
                    return $answer;
                });
                $password = $helper->ask($input, $output, $question);
            }

            if (!$fullname) {
                $question = new Question('Nombre completo: ');
                $question->setValidator(function ($answer) {
                    if (empty($answer)) {
                        throw new \RuntimeException('El nombre completo no puede estar vacío.');
                    }
                    return $answer;
                });
                $fullname = $helper->ask($input, $output, $question);
            }
        } else {
            // Modo no interactivo: verificar que todos los datos estén presentes
            if (!$username || !$email || !$password || !$fullname) {
                $io->error('Debe proporcionar todas las opciones en modo no interactivo: --username, --email, --password, --fullname');
                return Command::FAILURE;
            }
        }

        // Crear el usuario
        $usuario = new Usuario();
        $usuario->setNombreUsuario($username);
        $usuario->setEmail($email);
        $usuario->setNombreCompleto($fullname);
        $usuario->setRol($rolSuperAdmin);
        $usuario->setActivo(true);

        // Hashear la contraseña
        $hashedPassword = $this->passwordHasher->hashPassword($usuario, $password);
        $usuario->setContrasenaHash($hashedPassword);

        // Persistir
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Usuario Super Admin "%s" (%s) creado correctamente.',
            $username,
            $email
        ));

        // Mostrar resumen (sin la contraseña en texto plano)
        $io->table(
            ['Campo', 'Valor'],
            [
                ['Usuario', $username],
                ['Email', $email],
                ['Nombre completo', $fullname],
                ['Rol', 'ROLE_SUPER_ADMIN'],
                ['Activo', 'Sí'],
            ]
        );

        return Command::SUCCESS;
    }
}
