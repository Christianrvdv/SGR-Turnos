<?php

namespace App\Entity;

use App\Enum\EstadoConfiguracion;
use App\Repository\ConfiguracionDiariaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfiguracionDiariaRepository::class)]
#[ORM\Table(name: 'configuracion_diaria')]
#[ORM\UniqueConstraint(name: 'uk_configuracion_diaria_servicio_fecha', columns: ['servicio_id', 'fecha'])]
class ConfiguracionDiaria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Servicio::class, inversedBy: 'configuracionesDiarias')]
    #[ORM\JoinColumn(name: 'servicio_id', referencedColumnName: 'id', nullable: false)]
    private ?Servicio $servicio = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(name: 'monto_cargado', type: 'decimal', precision: 12, scale: 2)]
    private ?string $montoCargado = null;

    #[ORM\Column(name: 'limite_por_persona', type: 'decimal', precision: 10, scale: 2)]
    private ?string $limitePorPersona = null;

    #[ORM\Column(name: 'porcentaje_reserva', type: 'decimal', precision: 5, scale: 2, options: ['default' => 0.00])]
    private string $porcentajeReserva = '0.00';

    #[ORM\Column(name: 'tickets_generados', type: 'integer', options: ['unsigned' => true])]
    private ?int $ticketsGenerados = null;

    #[ORM\Column(name: 'tickets_restantes', type: 'integer', options: ['unsigned' => true])]
    private ?int $ticketsRestantes = null;

    #[ORM\Column(type: 'string', length: 20, enumType: EstadoConfiguracion::class)]
    private EstadoConfiguracion $estado = EstadoConfiguracion::ABIERTA;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'configuracionesDiarias')]
    #[ORM\JoinColumn(name: 'creado_por', referencedColumnName: 'id', nullable: false)]
    private ?Usuario $creadoPor = null;

    #[ORM\Column(name: 'creado_en', type: 'datetime')]
    private \DateTimeInterface $creadoEn;

    #[ORM\Column(name: 'actualizado_en', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $actualizadoEn = null;

    #[ORM\OneToMany(mappedBy: 'configuracionDiaria', targetEntity: Turno::class)]
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

    public function getServicio(): ?Servicio
    {
        return $this->servicio;
    }

    public function setServicio(?Servicio $servicio): self
    {
        $this->servicio = $servicio;
        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function getMontoCargado(): ?string
    {
        return $this->montoCargado;
    }

    public function setMontoCargado(string $montoCargado): self
    {
        $this->montoCargado = $montoCargado;
        return $this;
    }

    public function getLimitePorPersona(): ?string
    {
        return $this->limitePorPersona;
    }

    public function setLimitePorPersona(string $limitePorPersona): self
    {
        $this->limitePorPersona = $limitePorPersona;
        return $this;
    }

    public function getPorcentajeReserva(): string
    {
        return $this->porcentajeReserva;
    }

    public function setPorcentajeReserva(string $porcentajeReserva): self
    {
        $this->porcentajeReserva = $porcentajeReserva;
        return $this;
    }

    public function getTicketsGenerados(): ?int
    {
        return $this->ticketsGenerados;
    }

    public function setTicketsGenerados(int $ticketsGenerados): self
    {
        $this->ticketsGenerados = $ticketsGenerados;
        return $this;
    }

    public function getTicketsRestantes(): ?int
    {
        return $this->ticketsRestantes;
    }

    public function setTicketsRestantes(int $ticketsRestantes): self
    {
        $this->ticketsRestantes = $ticketsRestantes;
        return $this;
    }

    public function getEstado(): EstadoConfiguracion
    {
        return $this->estado;
    }

    public function setEstado(EstadoConfiguracion $estado): self
    {
        $this->estado = $estado;
        return $this;
    }

    public function getCreadoPor(): ?Usuario
    {
        return $this->creadoPor;
    }

    public function setCreadoPor(?Usuario $creadoPor): self
    {
        $this->creadoPor = $creadoPor;
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
            $turno->setConfiguracionDiaria($this);
        }
        return $this;
    }

    public function removeTurno(Turno $turno): self
    {
        if ($this->turnos->removeElement($turno)) {
            if ($turno->getConfiguracionDiaria() === $this) {
                $turno->setConfiguracionDiaria(null);
            }
        }
        return $this;
    }
}
