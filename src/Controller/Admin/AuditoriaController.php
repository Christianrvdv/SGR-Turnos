<?php
// src/Controller/Admin/AuditoriaController.php

namespace App\Controller\Admin;

use App\Repository\AuditoriaRepository;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AuditoriaController extends AbstractController
{
    public function __construct(
        private readonly AuditoriaRepository $auditoriaRepository,
        private readonly UsuarioRepository $usuarioRepository,
    ) {
    }

    #[Route('/admin/auditoria', name: 'admin_auditoria_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 15;
        $usuarioId = $request->query->get('usuario');
        $tipoAccion = $request->query->get('accion');
        $fechaInicio = $request->query->get('fecha_inicio');
        $fechaFin = $request->query->get('fecha_fin');

        $queryBuilder = $this->auditoriaRepository->createQueryBuilder('a')
            ->leftJoin('a.usuario', 'u')
            ->addSelect('u')
            ->orderBy('a.creadoEn', 'DESC');

        if ($usuarioId) {
            $queryBuilder
                ->andWhere('a.usuario = :usuario')
                ->setParameter('usuario', $usuarioId);
        }

        if ($tipoAccion) {
            $queryBuilder
                ->andWhere('a.tipoAccion = :tipo')
                ->setParameter('tipo', $tipoAccion);
        }

        if ($fechaInicio) {
            $queryBuilder
                ->andWhere('a.creadoEn >= :inicio')
                ->setParameter('inicio', new \DateTime($fechaInicio . ' 00:00:00'));
        }

        if ($fechaFin) {
            $queryBuilder
                ->andWhere('a.creadoEn <= :fin')
                ->setParameter('fin', new \DateTime($fechaFin . ' 23:59:59'));
        }

        $totalRegistros = (clone $queryBuilder)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
        $auditorias = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $totalPages = (int) ceil($totalRegistros / $limit);

        // Obtener usuarios para filtro
        $usuarios = $this->usuarioRepository->findAll();

        // Tipos de acción únicos para filtro
        $tiposAccion = $this->auditoriaRepository->createQueryBuilder('a')
            ->select('DISTINCT a.tipoAccion')
            ->orderBy('a.tipoAccion', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->render('admin/auditoria/index.html.twig', [
            'auditorias' => $auditorias,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRegistros' => $totalRegistros,
            'limit' => $limit,
            'usuarios' => $usuarios,
            'tiposAccion' => $tiposAccion,
            'filtros' => [
                'usuario' => $usuarioId,
                'accion' => $tipoAccion,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }
}
