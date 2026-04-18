<?php

namespace App\Entity;

use App\Repository\AuditoriaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditoriaRepository::class)]
#[ORM\Table(name: 'auditoria')]
class Auditoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'auditorias')]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: true)]
    private ?Usuario $usuario = null;

    #[ORM\Column(name: 'tipo_accion', type: 'string', length: 50)]
    private ?string $tipoAccion = null;

    #[ORM\Column(name: 'entidad_afectada', type: 'string', length: 50, nullable: true)]
    private ?string $entidadAfectada = null;

    #[ORM\Column(name: 'entidad_id', type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $entidadId = null;

    #[ORM\Column(name: 'datos_antes', type: 'json', nullable: true)]
    private ?array $datosAntes = null;

    #[ORM\Column(name: 'datos_despues', type: 'json', nullable: true)]
    private ?array $datosDespues = null;

    #[ORM\Column(name: 'ip_origen', type: 'string', length: 45, nullable: true)]
    private ?string $ipOrigen = null;

    #[ORM\Column(name: 'user_agent', type: 'string', length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'creado_en', type: 'datetime')]
    private \DateTimeInterface $creadoEn;

    public function __construct()
    {
        $this->creadoEn = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getTipoAccion(): ?string
    {
        return $this->tipoAccion;
    }

    public function setTipoAccion(string $tipoAccion): self
    {
        $this->tipoAccion = $tipoAccion;
        return $this;
    }

    public function getEntidadAfectada(): ?string
    {
        return $this->entidadAfectada;
    }

    public function setEntidadAfectada(?string $entidadAfectada): self
    {
        $this->entidadAfectada = $entidadAfectada;
        return $this;
    }

    public function getEntidadId(): ?int
    {
        return $this->entidadId;
    }

    public function setEntidadId(?int $entidadId): self
    {
        $this->entidadId = $entidadId;
        return $this;
    }

    public function getDatosAntes(): ?array
    {
        return $this->datosAntes;
    }

    public function setDatosAntes(?array $datosAntes): self
    {
        $this->datosAntes = $datosAntes;
        return $this;
    }

    public function getDatosDespues(): ?array
    {
        return $this->datosDespues;
    }

    public function setDatosDespues(?array $datosDespues): self
    {
        $this->datosDespues = $datosDespues;
        return $this;
    }

    public function getIpOrigen(): ?string
    {
        return $this->ipOrigen;
    }

    public function setIpOrigen(?string $ipOrigen): self
    {
        $this->ipOrigen = $ipOrigen;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getCreadoEn(): \DateTimeInterface
    {
        return $this->creadoEn;
    }

    public function setCreadoEn(\DateTimeInterface $creadoEn): self
    {
        $this->creadoEn = $creadoEn;
        return $this;
    }
}
