<?php

namespace App\Entity;

use App\Repository\OfertaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OfertaRepository::class)
 */
class Oferta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0)
     */
    private $monto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion_oferta;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tipo_descuento;

    /**
     * @ORM\Column(type="integer")
     */
    private $stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonto(): ?string
    {
        return $this->monto;
    }

    public function setMonto(string $monto): self
    {
        $this->monto = $monto;

        return $this;
    }

    public function getDescripcionOferta(): ?string
    {
        return $this->descripcion_oferta;
    }

    public function setDescripcionOferta(string $descripcion_oferta): self
    {
        $this->descripcion_oferta = $descripcion_oferta;

        return $this;
    }

    public function getTipoDescuento(): ?string
    {
        return $this->tipo_descuento;
    }

    public function setTipoDescuento(string $tipo_descuento): self
    {
        $this->tipo_descuento = $tipo_descuento;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }
}
