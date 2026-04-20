<?php
// src/Service/AuditoriaService.php

namespace App\Service;

use App\Entity\Auditoria;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class AuditoriaService implements AuditoriaInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Registra una acción en la auditoría.
     *
     * @param string $tipoAccion Tipo de acción (ej: 'CREATE', 'UPDATE', 'DELETE')
     * @param string $entidadAfectada Nombre de la entidad (ej: 'Servicio')
     * @param int|null $entidadId ID de la entidad afectada
     * @param array|null $datosAntes Datos antes del cambio (opcional)
     * @param array|null $datosDespues Datos después del cambio (opcional)
     */
    public function registrar(
        string $tipoAccion,
        string $entidadAfectada,
        ?int $entidadId = null,
        ?array $datosAntes = null,
        ?array $datosDespues = null
    ): void {
        $auditoria = new Auditoria();

        /** @var Usuario|null $usuario */
        $usuario = $this->security->getUser();
        $auditoria->setUsuario($usuario instanceof Usuario ? $usuario : null);

        $auditoria->setTipoAccion($tipoAccion);
        $auditoria->setEntidadAfectada($entidadAfectada);
        $auditoria->setEntidadId($entidadId);
        $auditoria->setDatosAntes($datosAntes);
        $auditoria->setDatosDespues($datosDespues);

        // Obtener IP del request
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $auditoria->setIpOrigen($request->getClientIp());
            $auditoria->setUserAgent($request->headers->get('User-Agent'));
        }

        $this->entityManager->persist($auditoria);
        $this->entityManager->flush();
    }
}
