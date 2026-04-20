<?php
// src/EventSubscriber/LoginAuditSubscriber.php

namespace App\EventSubscriber;

use App\Entity\Usuario;
use App\Service\AuditoriaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuditoriaService $auditoriaService,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if (!$user instanceof Usuario) {
            return;
        }

        $request = $event->getRequest();

        $this->auditoriaService->registrar(
            'LOGIN',
            'Usuario',
            $user->getId(),
            null,
            [
                'nombreUsuario' => $user->getNombreUsuario(),
                'nombreCompleto' => $user->getNombreCompleto(),
                'email' => $user->getEmail(),
                'ip' => $request->getClientIp(),
                'userAgent' => $request->headers->get('User-Agent'),
            ]
        );
    }
}
