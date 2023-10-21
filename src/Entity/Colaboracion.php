<?php

namespace App\Entity;

use App\Repository\ColaboracionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ColaboracionRepository::class)
 */
class Colaboracion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $puntaje;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $tipoVoto;

    /**
     * @ORM\ManyToOne(targetEntity=Oferta::class, inversedBy="colaboracions")
     */
    private $oferta;

    /**
     * @ORM\ManyToOne(targetEntity=Producto::class, inversedBy="colaboracions")
     */
    private $producto;

    /**
     * @ORM\ManyToOne(targetEntity=Comercio::class, inversedBy="colaboracions")
     */
    private $comercio;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="colaboracions")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sujeto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPuntaje(): ?int
    {
        return $this->puntaje;
    }

    public function setPuntaje(?int $puntaje): self
    {
        $this->puntaje = $puntaje;

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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): self
    {
        $this->tipo = $tipo;

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

    public function getTipoVoto(): ?bool
    {
        return $this->tipoVoto;
    }

    public function setTipoVoto(?bool $tipoVoto): self
    {
        $this->tipoVoto = $tipoVoto;

        return $this;
    }

    public function getOferta(): ?Oferta
    {
        return $this->oferta;
    }

    public function setOferta(?Oferta $oferta): self
    {
        $this->oferta = $oferta;

        return $this;
    }

    public function getProducto(): ?Producto
    {
        return $this->producto;
    }

    public function setProducto(?Producto $producto): self
    {
        $this->producto = $producto;

        return $this;
    }

    public function getComercio(): ?Comercio
    {
        return $this->comercio;
    }

    public function setComercio(?Comercio $comercio): self
    {
        $this->comercio = $comercio;

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

    public function getSujeto(): ?string
    {
        return $this->sujeto;
    }

    public function setSujeto(?string $sujeto): self
    {
        $this->sujeto = $sujeto;

        return $this;
    }
}
