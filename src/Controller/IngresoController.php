<?php
// src/Controller/IngresoController.php

namespace App\Controller;

use App\Entity\Cliente;
use App\Repository\ClienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IngresoController extends AbstractController
{
    public function __construct(
        private readonly ClienteRepository      $clienteRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/ingreso', name: 'app_ingreso_ci')]
    public function index(): Response
    {
        return $this->render('ingreso/index.html.twig');
    }

    #[Route('/ingreso/procesar', name: 'app_ingreso_procesar', methods: ['POST'])]
    public function procesar(Request $request): Response
    {
        $ci = $request->request->get('ci', '');
        $ci = preg_replace('/[^0-9]/', '', $ci); // Solo números

        if (empty($ci)) {
            $this->addFlash('error', 'Debe ingresar un número de carnet.');
            return $this->redirectToRoute('app_ingreso_ci');
        }

        // Buscar o crear cliente
        $cliente = $this->clienteRepository->findOneBy(['numeroIdentidad' => $ci]);
        if (!$cliente) {
            $cliente = new Cliente();
            $cliente->setNumeroIdentidad($ci);
            $this->entityManager->persist($cliente);
            $this->entityManager->flush();
        }

        // Guardar cliente en sesión para uso posterior
        $request->getSession()->set('cliente_id', $cliente->getId());

        // Redirigir al dashboard (o a la selección de turno)
        return $this->redirectToRoute('app_dashboard');
    }
}
