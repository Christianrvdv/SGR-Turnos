<?php

namespace App\Entity;

use App\Enum\EstadoTurno;
use App\Repository\TurnoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TurnoRepository::class)]
#[ORM\Table(name: 'turno')]
#[ORM\UniqueConstraint(name: 'uk_turno_fecha_numero', columns: ['configuracion_diaria_id', 'numero_turno'])]
#[ORM\Index(name: 'idx_turno_cliente_estado_fecha', columns: ['cliente_id', 'estado', 'fecha_uso'])]
class Turno
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ConfiguracionDiaria::class, inversedBy: 'turnos')]
    #[ORM\JoinColumn(name: 'configuracion_diaria_id', referencedColumnName: 'id', nullable: false)]
    private ?ConfiguracionDiaria $configuracionDiaria = null;

    #[ORM\ManyToOne(targetEntity: Servicio::class, inversedBy: 'turnos')]
    #[ORM\JoinColumn(name: 'servicio_id', referencedColumnName: 'id', nullable: false)]
    private ?Servicio $servicio = null;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'turnos')]
    #[ORM\JoinColumn(name: 'cliente_id', referencedColumnName: 'id', nullable: false)]
    private ?Cliente $cliente = null;

    #[ORM\Column(name: 'numero_turno', type: 'string', length: 10)]
    private ?string $numeroTurno = null;

    #[ORM\Column(name: 'monto_permitido', type: 'decimal', precision: 10, scale: 2)]
    private ?string $montoPermitido = null;

    #[ORM\Column(type: 'string', length: 20, enumType: EstadoTurno::class)]
    private EstadoTurno $estado = EstadoTurno::RESERVADO;

    #[ORM\Column(name: 'fecha_reserva', type: 'datetime')]
    private \DateTimeInterface $fechaReserva;

    #[ORM\Column(name: 'fecha_uso', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $fechaUso = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'turnosMarcados')]
    #[ORM\JoinColumn(name: 'marcado_por', referencedColumnName: 'id', nullable: true)]
    private ?Usuario $marcadoPor = null;

    #[ORM\Column(name: 'ip_registro', type: 'string', length: 45, nullable: true)]
    private ?string $ipRegistro = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $observaciones = null;

    public function __construct()
    {
        $this->fechaReserva = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfiguracionDiaria(): ?ConfiguracionDiaria
    {
        return $this->configuracionDiaria;
    }

    public function setConfiguracionDiaria(?ConfiguracionDiaria $configuracionDiaria): self
    {
        $this->configuracionDiaria = $configuracionDiaria;
        // Sincronizar servicio con el de la configuración
        if ($configuracionDiaria) {
            $this->servicio = $configuracionDiaria->getServicio();
        }
        return $this;
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

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): self
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function getNumeroTurno(): ?string
    {
        return $this->numeroTurno;
    }

    public function setNumeroTurno(string $numeroTurno): self
    {
        $this->numeroTurno = $numeroTurno;
        return $this;
    }

    public function getMontoPermitido(): ?string
    {
        return $this->montoPermitido;
    }

    public function setMontoPermitido(string $montoPermitido): self
    {
        $this->montoPermitido = $montoPermitido;
        return $this;
    }

    public function getEstado(): EstadoTurno
    {
        return $this->estado;
    }

    public function setEstado(EstadoTurno $estado): self
    {
        $this->estado = $estado;
        return $this;
    }

    public function getFechaReserva(): \DateTimeInterface
    {
        return $this->fechaReserva;
    }

    public function setFechaReserva(\DateTimeInterface $fechaReserva): self
    {
        $this->fechaReserva = $fechaReserva;
        return $this;
    }

    public function getFechaUso(): ?\DateTimeInterface
    {
        return $this->fechaUso;
    }

    public function setFechaUso(?\DateTimeInterface $fechaUso): self
    {
        $this->fechaUso = $fechaUso;
        return $this;
    }

    public function getMarcadoPor(): ?Usuario
    {
        return $this->marcadoPor;
    }

    public function setMarcadoPor(?Usuario $marcadoPor): self
    {
        $this->marcadoPor = $marcadoPor;
        return $this;
    }

    public function getIpRegistro(): ?string
    {
        return $this->ipRegistro;
    }

    public function setIpRegistro(?string $ipRegistro): self
    {
        $this->ipRegistro = $ipRegistro;
        return $this;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): self
    {
        $this->observaciones = $observaciones;
        return $this;
    }
}
