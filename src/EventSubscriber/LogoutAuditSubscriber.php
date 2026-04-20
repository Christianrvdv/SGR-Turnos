<?php
// src/EventSubscriber/LogoutAuditSubscriber.php

namespace App\EventSubscriber;

use App\Entity\Usuario;
use App\Service\AuditoriaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuditoriaService $auditoriaService,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof Usuario) {
            return;
        }

        $request = $event->getRequest();

        $this->auditoriaService->registrar(
            'LOGOUT',
            'Usuario',
            $user->getId(),
            null,
            [
                'nombreUsuario' => $user->getNombreUsuario(),
                'nombreCompleto' => $user->getNombreCompleto(),
                'ip' => $request->getClientIp(),
            ]
        );
    }
}
