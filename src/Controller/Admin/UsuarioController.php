<?php
// src/Controller/Admin/UsuarioController.php

namespace App\Controller\Admin;

use App\Entity\Usuario;
use App\Repository\RolRepository;
use App\Repository\UsuarioRepository;
use App\Service\AuditoriaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UsuarioController extends AbstractController
{
    public function __construct(
        private readonly UsuarioRepository           $usuarioRepository,
        private readonly RolRepository               $rolRepository,
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AuditoriaService            $auditoriaService,
    )
    {
    }

    #[Route('/admin/usuarios', name: 'admin_usuarios_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $search = $request->query->get('search', '');

        $queryBuilder = $this->usuarioRepository->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r')
            ->addSelect('r')
            ->orderBy('u.nombreCompleto', 'ASC');

        if ($search) {
            $queryBuilder
                ->andWhere('u.nombreCompleto LIKE :search OR u.nombreUsuario LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $totalUsuarios = (clone $queryBuilder)->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();
        $usuarios = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $totalPages = (int)ceil($totalUsuarios / $limit);

        $roles = $this->rolRepository->findAll();

        return $this->render('admin/usuarios/index.html.twig', [
            'usuarios' => $usuarios,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsuarios' => $totalUsuarios,
            'search' => $search,
            'limit' => $limit,
            'roles' => $roles,
        ]);
    }

    #[Route('/admin/usuarios/crear', name: 'admin_usuarios_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('usuario_create', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $nombreCompleto = $request->request->get('nombreCompleto');
        $nombreUsuario = $request->request->get('nombreUsuario');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $rolId = $request->request->get('rol');
        $activo = $request->request->getBoolean('activo');

        // Validaciones básicas
        if (!$nombreCompleto || !$nombreUsuario || !$password || !$rolId) {
            $this->addFlash('error', 'Todos los campos obligatorios deben completarse.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        // Verificar unicidad
        $existente = $this->usuarioRepository->findOneBy(['nombreUsuario' => $nombreUsuario]);
        if ($existente) {
            $this->addFlash('error', 'El nombre de usuario ya está en uso.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $rol = $this->rolRepository->find($rolId);
        if (!$rol) {
            $this->addFlash('error', 'Rol no válido.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $usuario = new Usuario();
        $usuario->setNombreCompleto($nombreCompleto);
        $usuario->setNombreUsuario($nombreUsuario);
        $usuario->setEmail($email ?: null);
        $usuario->setRol($rol);
        $usuario->setActivo($activo);

        $hashedPassword = $this->passwordHasher->hashPassword($usuario, $password);
        $usuario->setContrasenaHash($hashedPassword);

        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        // Registrar en auditoría (sin exponer la contraseña)
        $this->auditoriaService->registrar(
            'CREATE',
            'Usuario',
            $usuario->getId(),
            null,
            [
                'nombreCompleto' => $usuario->getNombreCompleto(),
                'nombreUsuario' => $usuario->getNombreUsuario(),
                'email' => $usuario->getEmail(),
                'rol' => $rol->getNombre(),
                'activo' => $usuario->isActivo(),
            ]
        );

        $this->addFlash('success', 'Usuario creado correctamente.');
        return $this->redirectToRoute('admin_usuarios_index');
    }
}
