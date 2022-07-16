<?php

namespace App\Entity;

use App\Repository\ComercioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ComercioRepository::class)
 */
class Comercio
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $cuitComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombreComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $direccionComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $emailComercio;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaRegistroComercio;

    /**
     * @ORM\Column(type="datetime")
     */
    private $horaAperturaComercio;

    /**
     * @ORM\Column(type="float")
     */
    private $latitudComercio;

    /**
     * @ORM\Column(type="float")
     */
    private $longitudComercio;

    /**
     * @ORM\Column(type="datetime")
     */
    private $horaCierreComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $estadoComercio;

    /**
     * @ORM\Column(type="string", length=800)
     */
    private $descripcionComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telefonoComercio;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sucursal;

    /**
     * @ORM\Column(type="integer")
     */
    private $idLocalidad;

    /**
     * @ORM\Column(type="integer")
     */
    private $idUsuario;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="Comercio")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Localidad::class, inversedBy="comercios")
     */
    private $localidad;

    /**
     * @ORM\OneToMany(targetEntity=Oferta::class, mappedBy="comercio")
     */
    private $oferta;

    public function __construct()
    {
        $this->oferta = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuitComercio(): ?int
    {
        return $this->cuitComercio;
    }

    public function setCuitComercio(int $cuitComercio): self
    {
        $this->cuitComercio = $cuitComercio;

        return $this;
    }

    public function getNombreComercio(): ?string
    {
        return $this->nombreComercio;
    }

    public function setNombreComercio(string $nombreComercio): self
    {
        $this->nombreComercio = $nombreComercio;

        return $this;
    }

    public function getDireccionComercio(): ?string
    {
        return $this->direccionComercio;
    }

    public function setDireccionComercio(string $direccionComercio): self
    {
        $this->direccionComercio = $direccionComercio;

        return $this;
    }

    public function getEmailComercio(): ?string
    {
        return $this->emailComercio;
    }

    public function setEmailComercio(string $emailComercio): self
    {
        $this->emailComercio = $emailComercio;

        return $this;
    }

    public function getFechaRegistroComercio(): ?\DateTimeInterface
    {
        return $this->fechaRegistroComercio;
    }

    public function setFechaRegistroComercio(\DateTimeInterface $fechaRegistroComercio): self
    {
        $this->fechaRegistroComercio = $fechaRegistroComercio;

        return $this;
    }

    public function getHoraAperturaComercio(): ?\DateTimeInterface
    {
        return $this->horaAperturaComercio;
    }

    public function setHoraAperturaComercio(\DateTimeInterface $horaAperturaComercio): self
    {
        $this->horaAperturaComercio = $horaAperturaComercio;

        return $this;
    }

    public function getLatitudComercio(): ?float
    {
        return $this->latitudComercio;
    }

    public function setLatitudComercio(float $latitudComercio): self
    {
        $this->latitudComercio = $latitudComercio;

        return $this;
    }

    public function getLongitudComercio(): ?float
    {
        return $this->longitudComercio;
    }

    public function setLongitudComercio(float $longitudComercio): self
    {
        $this->longitudComercio = $longitudComercio;

        return $this;
    }

    public function getHoraCierreComercio(): ?\DateTimeInterface
    {
        return $this->horaCierreComercio;
    }

    public function setHoraCierreComercio(\DateTimeInterface $horaCierreComercio): self
    {
        $this->horaCierreComercio = $horaCierreComercio;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getEstadoComercio(): ?string
    {
        return $this->estadoComercio;
    }

    public function setEstadoComercio(string $estadoComercio): self
    {
        $this->estadoComercio = $estadoComercio;

        return $this;
    }

    public function getDescripcionComercio(): ?string
    {
        return $this->descripcionComercio;
    }

    public function setDescripcionComercio(string $descripcionComercio): self
    {
        $this->descripcionComercio = $descripcionComercio;

        return $this;
    }

    public function getTelefonoComercio(): ?string
    {
        return $this->telefonoComercio;
    }

    public function setTelefonoComercio(string $telefonoComercio): self
    {
        $this->telefonoComercio = $telefonoComercio;

        return $this;
    }

    public function getSucursal(): ?bool
    {
        return $this->sucursal;
    }

    public function setSucursal(bool $sucursal): self
    {
        $this->sucursal = $sucursal;

        return $this;
    }

    public function getIdLocalidad(): ?int
    {
        return $this->idLocalidad;
    }

    public function setIdLocalidad(int $idLocalidad): self
    {
        $this->idLocalidad = $idLocalidad;

        return $this;
    }

    public function getIdUsuario(): ?int
    {
        return $this->idUsuario;
    }

    public function setIdUsuario(int $idUsuario): self
    {
        $this->idUsuario = $idUsuario;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLocalidad(): ?Localidad
    {
        return $this->localidad;
    }

    public function setLocalidad(?Localidad $localidad): self
    {
        $this->localidad = $localidad;

        return $this;
    }

    /**
     * @return Collection<int, Oferta>
     */
    public function getOferta(): Collection
    {
        return $this->oferta;
    }

    public function addOfertum(Oferta $ofertum): self
    {
        if (!$this->oferta->contains($ofertum)) {
            $this->oferta[] = $ofertum;
            $ofertum->setComercio($this);
        }

        return $this;
    }

    public function removeOfertum(Oferta $ofertum): self
    {
        if ($this->oferta->removeElement($ofertum)) {
            // set the owning side to null (unless already changed)
            if ($ofertum->getComercio() === $this) {
                $ofertum->setComercio(null);
            }
        }

        return $this;
    }
}
