<?php
// src/Controller/Admin/ServicioController.php

namespace App\Controller\Admin;

use App\Repository\ServicioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ServicioController extends AbstractController
{
    public function __construct(
        private readonly ServicioRepository $servicioRepository,
    )
    {
    }

    #[Route('/admin/servicios', name: 'admin_servicios_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $search = $request->query->get('search', '');

        $queryBuilder = $this->servicioRepository->createQueryBuilder('s')
            ->orderBy('s.nombre', 'ASC');

        if ($search) {
            $queryBuilder
                ->andWhere('s.nombre LIKE :search OR s.codigo LIKE :search OR s.descripcion LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $totalServicios = (clone $queryBuilder)->select('COUNT(s.id)')->getQuery()->getSingleScalarResult();
        $servicios = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $totalPages = (int)ceil($totalServicios / $limit);

        return $this->render('admin/servicios/index.html.twig', [
            'servicios' => $servicios,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalServicios' => $totalServicios,
            'search' => $search,
            'limit' => $limit,
        ]);
    }
}
