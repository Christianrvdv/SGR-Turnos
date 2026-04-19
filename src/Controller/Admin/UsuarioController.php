<?php
// src/Controller/Admin/UsuarioController.php

namespace App\Controller\Admin;

use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UsuarioController extends AbstractController
{
    public function __construct(
        private readonly UsuarioRepository $usuarioRepository,
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

        return $this->render('admin/usuarios/index.html.twig', [
            'usuarios' => $usuarios,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsuarios' => $totalUsuarios,
            'search' => $search,
            'limit' => $limit,
        ]);
    }
}
