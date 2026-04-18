<?php

namespace App\Entity;

use App\Repository\ClienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClienteRepository::class)]
#[ORM\Table(name: 'cliente')]
#[ORM\UniqueConstraint(name: 'uk_cliente_numero_identidad', columns: ['numero_identidad'])]
#[ORM\Index(name: 'idx_cliente_numero_tarjeta', columns: ['numero_tarjeta'])]
class Cliente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(name: 'numero_identidad', type: 'string', length: 20)]
    private ?string $numeroIdentidad = null;

    #[ORM\Column(name: 'numero_tarjeta', type: 'string', length: 20, nullable: true)]
    private ?string $numeroTarjeta = null;

    #[ORM\Column(name: 'nombre_completo', type: 'string', length: 150, nullable: true)]
    private ?string $nombreCompleto = null;

    #[ORM\Column(name: 'numero_telefono', type: 'string', length: 20, nullable: true)]
    private ?string $numeroTelefono = null;

    #[ORM\Column(name: 'creado_en', type: 'datetime')]
    private \DateTimeInterface $creadoEn;

    #[ORM\Column(name: 'actualizado_en', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $actualizadoEn = null;

    #[ORM\OneToMany(mappedBy: 'cliente', targetEntity: Turno::class)]
    private Collection $turnos;

    public function __construct()
    {
        $this->creadoEn = new \DateTime();
        $this->turnos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroIdentidad(): ?string
    {
        return $this->numeroIdentidad;
    }

    public function setNumeroIdentidad(string $numeroIdentidad): self
    {
        $this->numeroIdentidad = $numeroIdentidad;
        return $this;
    }

    public function getNumeroTarjeta(): ?string
    {
        return $this->numeroTarjeta;
    }

    public function setNumeroTarjeta(?string $numeroTarjeta): self
    {
        $this->numeroTarjeta = $numeroTarjeta;
        return $this;
    }

    public function getNombreCompleto(): ?string
    {
        return $this->nombreCompleto;
    }

    public function setNombreCompleto(?string $nombreCompleto): self
    {
        $this->nombreCompleto = $nombreCompleto;
        return $this;
    }

    public function getNumeroTelefono(): ?string
    {
        return $this->numeroTelefono;
    }

    public function setNumeroTelefono(?string $numeroTelefono): self
    {
        $this->numeroTelefono = $numeroTelefono;
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

    public function getActualizadoEn(): ?\DateTimeInterface
    {
        return $this->actualizadoEn;
    }

    public function setActualizadoEn(?\DateTimeInterface $actualizadoEn): self
    {
        $this->actualizadoEn = $actualizadoEn;
        return $this;
    }

    /**
     * @return Collection<int, Turno>
     */
    public function getTurnos(): Collection
    {
        return $this->turnos;
    }

    public function addTurno(Turno $turno): self
    {
        if (!$this->turnos->contains($turno)) {
            $this->turnos[] = $turno;
            $turno->setCliente($this);
        }
        return $this;
    }

    public function removeTurno(Turno $turno): self
    {
        if ($this->turnos->removeElement($turno)) {
            if ($turno->getCliente() === $this) {
                $turno->setCliente(null);
            }
        }
        return $this;
    }
}
