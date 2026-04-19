<?php

namespace App\Controller\Admin;

use App\Service\DashboardInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly DashboardInterface $dashboardService,
    )
    {
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        $datos = $this->dashboardService->getDashboardData();

        return $this->render('admin/dashboard.html.twig', $datos);
    }
}
