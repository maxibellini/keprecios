<?php

namespace App\Entity;

use App\Repository\ConfianzaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfianzaRepository::class)
 */
class Confianza
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $nombre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limiteSuperior;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limiteInferior;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $tipo;

    /**
     * @ORM\OneToMany(targetEntity=Oferta::class, mappedBy="confianza")
     */
    private $oferta;

    /**
     * @ORM\OneToMany(targetEntity=Comercio::class, mappedBy="confianza")
     */
    private $comercio;

    /**
     * @ORM\OneToMany(targetEntity=Producto::class, mappedBy="confianza")
     */
    private $producto;

    public function __construct()
    {
        $this->oferta = new ArrayCollection();
        $this->comercio = new ArrayCollection();
        $this->producto = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getLimiteSuperior(): ?int
    {
        return $this->limiteSuperior;
    }

    public function setLimiteSuperior(?int $limiteSuperior): self
    {
        $this->limiteSuperior = $limiteSuperior;

        return $this;
    }

    public function getLimiteInferior(): ?int
    {
        return $this->limiteInferior;
    }

    public function setLimiteInferior(?int $limiteInferior): self
    {
        $this->limiteInferior = $limiteInferior;

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
            $ofertum->setConfianza($this);
        }

        return $this;
    }

    public function removeOfertum(Oferta $ofertum): self
    {
        if ($this->oferta->removeElement($ofertum)) {
            // set the owning side to null (unless already changed)
            if ($ofertum->getConfianza() === $this) {
                $ofertum->setConfianza(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comercio>
     */
    public function getComercio(): Collection
    {
        return $this->comercio;
    }

    public function addComercio(Comercio $comercio): self
    {
        if (!$this->comercio->contains($comercio)) {
            $this->comercio[] = $comercio;
            $comercio->setConfianza($this);
        }

        return $this;
    }

    public function removeComercio(Comercio $comercio): self
    {
        if ($this->comercio->removeElement($comercio)) {
            // set the owning side to null (unless already changed)
            if ($comercio->getConfianza() === $this) {
                $comercio->setConfianza(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Producto>
     */
    public function getProducto(): Collection
    {
        return $this->producto;
    }

    public function addProducto(Producto $producto): self
    {
        if (!$this->producto->contains($producto)) {
            $this->producto[] = $producto;
            $producto->setConfianza($this);
        }

        return $this;
    }

    public function removeProducto(Producto $producto): self
    {
        if ($this->producto->removeElement($producto)) {
            // set the owning side to null (unless already changed)
            if ($producto->getConfianza() === $this) {
                $producto->setConfianza(null);
            }
        }

        return $this;
    }
}
