<?php
// src/Controller/Admin/ServicioController.php

namespace App\Controller\Admin;

use App\Entity\Servicio;
use App\Repository\ServicioRepository;
use App\Service\AuditoriaService;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly AuditoriaService $auditoriaService,
    ) {
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
            'servicios'       => $servicios,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'totalServicios'  => $totalServicios,
            'search'          => $search,
            'limit'           => $limit,
        ]);
    }

    #[Route('/admin/servicios/crear', name: 'admin_servicios_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('servicio_create', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $codigo = trim($request->request->get('codigo', ''));
        $nombre = trim($request->request->get('nombre', ''));
        $descripcion = trim($request->request->get('descripcion', ''));
        $permiteReservaFutura = $request->request->getBoolean('permiteReservaFutura');
        $requiereControlFrecuencia = $request->request->getBoolean('requiereControlFrecuencia');
        $diasBloqueo = $request->request->getInt('diasBloqueo', 7);
        $activo = $request->request->getBoolean('activo');

        if (empty($codigo) || empty($nombre)) {
            $this->addFlash('error', 'Los campos Código y Nombre son obligatorios.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $existente = $this->servicioRepository->findOneBy(['codigo' => $codigo]);
        if ($existente) {
            $this->addFlash('error', 'Ya existe un servicio con ese código.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $servicio = new Servicio();
        $servicio->setCodigo($codigo);
        $servicio->setNombre($nombre);
        $servicio->setDescripcion($descripcion ?: null);
        $servicio->setPermiteReservaFutura($permiteReservaFutura);
        $servicio->setRequiereControlFrecuencia($requiereControlFrecuencia);
        $servicio->setDiasBloqueo($diasBloqueo);
        $servicio->setActivo($activo);

        $this->entityManager->persist($servicio);
        $this->entityManager->flush();

        $this->auditoriaService->registrar(
            'CREATE',
            'Servicio',
            $servicio->getId(),
            null,
            [
                'codigo'                  => $servicio->getCodigo(),
                'nombre'                  => $servicio->getNombre(),
                'descripcion'             => $servicio->getDescripcion(),
                'permiteReservaFutura'    => $servicio->isPermiteReservaFutura(),
                'requiereControlFrecuencia'=> $servicio->isRequiereControlFrecuencia(),
                'diasBloqueo'             => $servicio->getDiasBloqueo(),
                'activo'                  => $servicio->isActivo(),
            ]
        );

        $this->addFlash('success', 'Servicio creado correctamente.');
        return $this->redirectToRoute('admin_servicios_index');
    }

    #[Route('/admin/servicios/{id}/editar', name: 'admin_servicios_update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $servicio = $this->servicioRepository->find($id);
        if (!$servicio) {
            $this->addFlash('error', 'Servicio no encontrado.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('servicio_create', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $datosAntes = [
            'codigo'                  => $servicio->getCodigo(),
            'nombre'                  => $servicio->getNombre(),
            'descripcion'             => $servicio->getDescripcion(),
            'permiteReservaFutura'    => $servicio->isPermiteReservaFutura(),
            'requiereControlFrecuencia'=> $servicio->isRequiereControlFrecuencia(),
            'diasBloqueo'             => $servicio->getDiasBloqueo(),
            'activo'                  => $servicio->isActivo(),
        ];

        $codigo = trim($request->request->get('codigo', ''));
        $nombre = trim($request->request->get('nombre', ''));
        $descripcion = trim($request->request->get('descripcion', ''));
        $permiteReservaFutura = $request->request->getBoolean('permiteReservaFutura');
        $requiereControlFrecuencia = $request->request->getBoolean('requiereControlFrecuencia');
        $diasBloqueo = $request->request->getInt('diasBloqueo', 7);
        $activo = $request->request->getBoolean('activo');

        if (empty($codigo) || empty($nombre)) {
            $this->addFlash('error', 'Los campos Código y Nombre son obligatorios.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $existente = $this->servicioRepository->findOneBy(['codigo' => $codigo]);
        if ($existente && $existente->getId() !== $servicio->getId()) {
            $this->addFlash('error', 'Ya existe otro servicio con ese código.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $servicio->setCodigo($codigo);
        $servicio->setNombre($nombre);
        $servicio->setDescripcion($descripcion ?: null);
        $servicio->setPermiteReservaFutura($permiteReservaFutura);
        $servicio->setRequiereControlFrecuencia($requiereControlFrecuencia);
        $servicio->setDiasBloqueo($diasBloqueo);
        $servicio->setActivo($activo);
        $servicio->setActualizadoEn(new \DateTime());

        $this->entityManager->flush();

        $this->auditoriaService->registrar(
            'UPDATE',
            'Servicio',
            $servicio->getId(),
            $datosAntes,
            [
                'codigo'                  => $servicio->getCodigo(),
                'nombre'                  => $servicio->getNombre(),
                'descripcion'             => $servicio->getDescripcion(),
                'permiteReservaFutura'    => $servicio->isPermiteReservaFutura(),
                'requiereControlFrecuencia'=> $servicio->isRequiereControlFrecuencia(),
                'diasBloqueo'             => $servicio->getDiasBloqueo(),
                'activo'                  => $servicio->isActivo(),
            ]
        );

        $this->addFlash('success', 'Servicio actualizado correctamente.');
        return $this->redirectToRoute('admin_servicios_index');
    }

    #[Route('/admin/servicios/{id}/toggle-activo', name: 'admin_servicios_toggle_activo', methods: ['POST'])]
    public function toggleActivo(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $servicio = $this->servicioRepository->find($id);
        if (!$servicio) {
            $this->addFlash('error', 'Servicio no encontrado.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('servicio_toggle_' . $id, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $estadoAnterior = $servicio->isActivo();
        $servicio->setActivo(!$estadoAnterior);
        $servicio->setActualizadoEn(new \DateTime());
        $this->entityManager->flush();

        $this->auditoriaService->registrar(
            $servicio->isActivo() ? 'ACTIVAR' : 'DESACTIVAR',
            'Servicio',
            $servicio->getId(),
            ['activo' => $estadoAnterior],
            ['activo' => $servicio->isActivo()]
        );

        $this->addFlash('success', $servicio->isActivo() ? 'Servicio activado correctamente.' : 'Servicio desactivado correctamente.');
        return $this->redirectToRoute('admin_servicios_index');
    }

    #[Route('/admin/servicios/{id}/eliminar', name: 'admin_servicios_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $servicio = $this->servicioRepository->find($id);
        if (!$servicio) {
            $this->addFlash('error', 'Servicio no encontrado.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('servicio_delete_' . $id, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF inválido.');
            return $this->redirectToRoute('admin_servicios_index');
        }

        $datosServicio = [
            'codigo'                  => $servicio->getCodigo(),
            'nombre'                  => $servicio->getNombre(),
            'descripcion'             => $servicio->getDescripcion(),
            'permiteReservaFutura'    => $servicio->isPermiteReservaFutura(),
            'requiereControlFrecuencia'=> $servicio->isRequiereControlFrecuencia(),
            'diasBloqueo'             => $servicio->getDiasBloqueo(),
            'activo'                  => $servicio->isActivo(),
        ];

        $this->entityManager->remove($servicio);
        $this->entityManager->flush();

        $this->auditoriaService->registrar(
            'DELETE',
            'Servicio',
            $id,
            $datosServicio,
            null
        );

        $this->addFlash('success', 'Servicio eliminado correctamente.');
        return $this->redirectToRoute('admin_servicios_index');
    }
}
