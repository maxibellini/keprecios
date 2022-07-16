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
     * @ORM\Column(type="string", length=255)
     */
    private $id_oferta;

    /**
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="integer")
     */
    private $id_comercio;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_producto;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_usuario_pub;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_usuario_modif;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdOferta(): ?string
    {
        return $this->id_oferta;
    }

    public function setIdOferta(string $id_oferta): self
    {
        $this->id_oferta = $id_oferta;

        return $this;
    }

    public function getMonto(): ?int
    {
        return $this->monto;
    }

    public function setMonto(int $monto): self
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

    public function getIdComercio(): ?int
    {
        return $this->id_comercio;
    }

    public function setIdComercio(int $id_comercio): self
    {
        $this->id_comercio = $id_comercio;

        return $this;
    }

    public function getIdProducto(): ?int
    {
        return $this->id_producto;
    }

    public function setIdProducto(int $id_producto): self
    {
        $this->id_producto = $id_producto;

        return $this;
    }

    public function getIdUsuarioPub(): ?int
    {
        return $this->id_usuario_pub;
    }

    public function setIdUsuarioPub(int $id_usuario_pub): self
    {
        $this->id_usuario_pub = $id_usuario_pub;

        return $this;
    }

    public function getIdUsuarioModif(): ?int
    {
        return $this->id_usuario_modif;
    }

    public function setIdUsuarioModif(int $id_usuario_modif): self
    {
        $this->id_usuario_modif = $id_usuario_modif;

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
