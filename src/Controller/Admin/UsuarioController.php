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

        if (!$nombreCompleto || !$nombreUsuario || !$password || !$rolId) {
            $this->addFlash('error', 'Todos los campos obligatorios deben completarse.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

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

    #[Route('/admin/usuarios/{id}/editar', name: 'admin_usuarios_update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $usuario = $this->usuarioRepository->find($id);
        if (!$usuario) {
            $this->addFlash('error', 'Usuario no encontrado.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        if ($usuario === $this->getUser()) {
            $this->addFlash('error', 'No puedes modificar tu propio usuario desde aquí.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('usuario_edit_' . $id, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $datosAntes = [
            'nombreCompleto' => $usuario->getNombreCompleto(),
            'nombreUsuario' => $usuario->getNombreUsuario(),
            'email' => $usuario->getEmail(),
            'rol' => $usuario->getRol()?->getNombre(),
            'activo' => $usuario->isActivo(),
        ];

        $nombreCompleto = $request->request->get('nombreCompleto');
        $email = $request->request->get('email');
        $rolId = $request->request->get('rol');
        $activo = $request->request->getBoolean('activo');
        $password = $request->request->get('password');

        if (!$nombreCompleto || !$rolId) {
            $this->addFlash('error', 'Nombre completo y Rol son obligatorios.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $rol = $this->rolRepository->find($rolId);
        if (!$rol) {
            $this->addFlash('error', 'Rol no válido.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $usuario->setNombreCompleto($nombreCompleto);
        $usuario->setEmail($email ?: null);
        $usuario->setRol($rol);
        $usuario->setActivo($activo);
        $usuario->setActualizadoEn(new \DateTime());

        $passwordChanged = false;
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $this->addFlash('error', 'La contraseña debe tener al menos 8 caracteres.');
                return $this->redirectToRoute('admin_usuarios_index');
            }
            $hashedPassword = $this->passwordHasher->hashPassword($usuario, $password);
            $usuario->setContrasenaHash($hashedPassword);
            $passwordChanged = true;
        }

        $this->entityManager->flush();

        $datosDespues = [
            'nombreCompleto' => $usuario->getNombreCompleto(),
            'nombreUsuario' => $usuario->getNombreUsuario(),
            'email' => $usuario->getEmail(),
            'rol' => $usuario->getRol()?->getNombre(),
            'activo' => $usuario->isActivo(),
        ];
        if ($passwordChanged) {
            $datosDespues['passwordChanged'] = true;
        }

        $this->auditoriaService->registrar(
            'UPDATE',
            'Usuario',
            $usuario->getId(),
            $datosAntes,
            $datosDespues
        );

        $this->addFlash('success', 'Usuario actualizado correctamente.');
        return $this->redirectToRoute('admin_usuarios_index');
    }

    #[Route('/admin/usuarios/{id}/toggle-activo', name: 'admin_usuarios_toggle_activo', methods: ['POST'])]
    public function toggleActivo(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $usuario = $this->usuarioRepository->find($id);
        if (!$usuario) {
            $this->addFlash('error', 'Usuario no encontrado.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        if ($usuario === $this->getUser()) {
            $this->addFlash('error', 'No puedes modificar tu propio estado.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('usuario_toggle_' . $id, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $estadoAnterior = $usuario->isActivo();
        $usuario->setActivo(!$estadoAnterior);
        $usuario->setActualizadoEn(new \DateTime());
        $this->entityManager->flush();

        $this->auditoriaService->registrar(
            $usuario->isActivo() ? 'ACTIVAR' : 'DESACTIVAR',
            'Usuario',
            $usuario->getId(),
            ['activo' => $estadoAnterior],
            ['activo' => $usuario->isActivo()]
        );

        $this->addFlash('success', $usuario->isActivo() ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
        return $this->redirectToRoute('admin_usuarios_index');
    }

    #[Route('/admin/usuarios/{id}/eliminar', name: 'admin_usuarios_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $usuario = $this->usuarioRepository->find($id);
        if (!$usuario) {
            $this->addFlash('error', 'Usuario no encontrado.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        if ($usuario === $this->getUser()) {
            $this->addFlash('error', 'No puedes eliminar tu propio usuario.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('usuario_delete_' . $id, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('admin_usuarios_index');
        }

        $datosUsuario = [
            'nombreCompleto' => $usuario->getNombreCompleto(),
            'nombreUsuario' => $usuario->getNombreUsuario(),
            'email' => $usuario->getEmail(),
            'rol' => $usuario->getRol()?->getNombre(),
        ];

        $this->entityManager->remove($usuario);
        $this->entityManager->flush();

        $this->auditoriaService->registrar(
            'DELETE',
            'Usuario',
            $id,
            $datosUsuario,
            null
        );

        $this->addFlash('success', 'Usuario eliminado correctamente.');
        return $this->redirectToRoute('admin_usuarios_index');
    }
}
