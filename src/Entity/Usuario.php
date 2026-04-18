<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\Table(name: 'usuario')]
#[ORM\UniqueConstraint(name: 'uk_usuario_nombre_usuario', columns: ['nombre_usuario'])]
#[ORM\UniqueConstraint(name: 'uk_usuario_email', columns: ['email'])]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Rol::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'rol_id', referencedColumnName: 'id', nullable: false)]
    private ?Rol $rol = null;

    #[ORM\Column(name: 'nombre_usuario', type: 'string', length: 50)]
    private ?string $nombreUsuario = null;

    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'contrasena_hash', type: 'string', length: 255)]
    private ?string $contrasenaHash = null;

    #[ORM\Column(name: 'nombre_completo', type: 'string', length: 100)]
    private ?string $nombreCompleto = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $activo = true;

    #[ORM\Column(name: 'ultimo_acceso', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $ultimoAcceso = null;

    #[ORM\Column(name: 'creado_en', type: 'datetime')]
    private \DateTimeInterface $creadoEn;

    #[ORM\Column(name: 'actualizado_en', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $actualizadoEn = null;

    #[ORM\OneToMany(mappedBy: 'creadoPor', targetEntity: ConfiguracionDiaria::class)]
    private Collection $configuracionesDiarias;

    #[ORM\OneToMany(mappedBy: 'marcadoPor', targetEntity: Turno::class)]
    private Collection $turnosMarcados;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Auditoria::class)]
    private Collection $auditorias;

    public function __construct()
    {
        $this->creadoEn = new \DateTime();
        $this->configuracionesDiarias = new ArrayCollection();
        $this->turnosMarcados = new ArrayCollection();
        $this->auditorias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRol(): ?Rol
    {
        return $this->rol;
    }

    public function setRol(?Rol $rol): self
    {
        $this->rol = $rol;
        return $this;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->nombreUsuario;
    }

    public function setNombreUsuario(string $nombreUsuario): self
    {
        $this->nombreUsuario = $nombreUsuario;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getContrasenaHash(): ?string
    {
        return $this->contrasenaHash;
    }

    public function setContrasenaHash(string $contrasenaHash): self
    {
        $this->contrasenaHash = $contrasenaHash;
        return $this;
    }

    public function getNombreCompleto(): ?string
    {
        return $this->nombreCompleto;
    }

    public function setNombreCompleto(string $nombreCompleto): self
    {
        $this->nombreCompleto = $nombreCompleto;
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

    public function getUltimoAcceso(): ?\DateTimeInterface
    {
        return $this->ultimoAcceso;
    }

    public function setUltimoAcceso(?\DateTimeInterface $ultimoAcceso): self
    {
        $this->ultimoAcceso = $ultimoAcceso;
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

    // Implementación de UserInterface
    public function getRoles(): array
    {
        // Devolvemos el nombre del rol prefijado con ROLE_ (asumiendo que así se guarda)
        return [$this->rol?->getNombre() ?? 'ROLE_OPERADOR_BASICO'];
    }

    public function getPassword(): ?string
    {
        return $this->contrasenaHash;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return $this->nombreUsuario;
    }

    public function eraseCredentials(): void
    {
        // Si almacenas datos sensibles temporales, límpialos aquí
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
            $configuracionDiaria->setCreadoPor($this);
        }
        return $this;
    }

    public function removeConfiguracionDiaria(ConfiguracionDiaria $configuracionDiaria): self
    {
        if ($this->configuracionesDiarias->removeElement($configuracionDiaria)) {
            if ($configuracionDiaria->getCreadoPor() === $this) {
                $configuracionDiaria->setCreadoPor(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Turno>
     */
    public function getTurnosMarcados(): Collection
    {
        return $this->turnosMarcados;
    }

    public function addTurnoMarcado(Turno $turno): self
    {
        if (!$this->turnosMarcados->contains($turno)) {
            $this->turnosMarcados[] = $turno;
            $turno->setMarcadoPor($this);
        }
        return $this;
    }

    public function removeTurnoMarcado(Turno $turno): self
    {
        if ($this->turnosMarcados->removeElement($turno)) {
            if ($turno->getMarcadoPor() === $this) {
                $turno->setMarcadoPor(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Auditoria>
     */
    public function getAuditorias(): Collection
    {
        return $this->auditorias;
    }

    public function addAuditoria(Auditoria $auditoria): self
    {
        if (!$this->auditorias->contains($auditoria)) {
            $this->auditorias[] = $auditoria;
            $auditoria->setUsuario($this);
        }
        return $this;
    }

    public function removeAuditoria(Auditoria $auditoria): self
    {
        if ($this->auditorias->removeElement($auditoria)) {
            if ($auditoria->getUsuario() === $this) {
                $auditoria->setUsuario(null);
            }
        }
        return $this;
    }
}
