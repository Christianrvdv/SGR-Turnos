<?php

namespace App\Entity;

use App\Repository\ServicioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServicioRepository::class)]
#[ORM\Table(name: 'servicio')]
#[ORM\UniqueConstraint(name: 'uk_servicio_codigo', columns: ['codigo'])]
class Servicio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $codigo = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(name: 'permite_reserva_futura', type: 'boolean', options: ['default' => true])]
    private bool $permiteReservaFutura = true;

    #[ORM\Column(name: 'requiere_control_frecuencia', type: 'boolean', options: ['default' => true])]
    private bool $requiereControlFrecuencia = true;

    #[ORM\Column(name: 'dias_bloqueo', type: 'smallint', nullable: true, options: ['unsigned' => true])]
    private ?int $diasBloqueo = 7;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $activo = true;

    #[ORM\Column(name: 'creado_en', type: 'datetime')]
    private \DateTimeInterface $creadoEn;

    #[ORM\Column(name: 'actualizado_en', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $actualizadoEn = null;

    #[ORM\OneToMany(mappedBy: 'servicio', targetEntity: ConfiguracionDiaria::class)]
    private Collection $configuracionesDiarias;

    #[ORM\OneToMany(mappedBy: 'servicio', targetEntity: Turno::class)]
    private Collection $turnos;

    public function __construct()
    {
        $this->creadoEn = new \DateTime();
        $this->configuracionesDiarias = new ArrayCollection();
        $this->turnos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;
        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function isPermiteReservaFutura(): bool
    {
        return $this->permiteReservaFutura;
    }

    public function setPermiteReservaFutura(bool $permiteReservaFutura): self
    {
        $this->permiteReservaFutura = $permiteReservaFutura;
        return $this;
    }

    public function isRequiereControlFrecuencia(): bool
    {
        return $this->requiereControlFrecuencia;
    }

    public function setRequiereControlFrecuencia(bool $requiereControlFrecuencia): self
    {
        $this->requiereControlFrecuencia = $requiereControlFrecuencia;
        return $this;
    }

    public function getDiasBloqueo(): ?int
    {
        return $this->diasBloqueo;
    }

    public function setDiasBloqueo(?int $diasBloqueo): self
    {
        $this->diasBloqueo = $diasBloqueo;
        return $this;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;
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
     * @return Collection<int, ConfiguracionDiaria>
     */
    public function getConfiguracionesDiarias(): Collection
    {
        return $this->configuracionesDiarias;
    }

    public function addConfiguracionDiaria(ConfiguracionDiaria $configuracionDiaria): self
    {
        if (!$this->configuracionesDiarias->contains($configuracionDiaria)) {
            $this->configuracionesDiarias[] = $configuracionDiaria;
            $configuracionDiaria->setServicio($this);
        }
        return $this;
    }

    public function removeConfiguracionDiaria(ConfiguracionDiaria $configuracionDiaria): self
    {
        if ($this->configuracionesDiarias->removeElement($configuracionDiaria)) {
            if ($configuracionDiaria->getServicio() === $this) {
                $configuracionDiaria->setServicio(null);
            }
        }
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
            $turno->setServicio($this);
        }
        return $this;
    }

    public function removeTurno(Turno $turno): self
    {
        if ($this->turnos->removeElement($turno)) {
            if ($turno->getServicio() === $this) {
                $turno->setServicio(null);
            }
        }
        return $this;
    }
}
