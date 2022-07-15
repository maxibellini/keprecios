<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductoRepository::class)
 */
class Producto
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
    private $gtin;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $marca_producto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion_producto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categoria_producto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $net_content;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $compania_producto;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado_producto;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="productos")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Pais::class, inversedBy="productos")
     */
    private $pais;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function setGtin(string $gtin): self
    {
        $this->gtin = $gtin;

        return $this;
    }

    public function getMarcaProducto(): ?string
    {
        return $this->marca_producto;
    }

    public function setMarcaProducto(string $marca_producto): self
    {
        $this->marca_producto = $marca_producto;

        return $this;
    }

    public function getDescripcionProducto(): ?string
    {
        return $this->descripcion_producto;
    }

    public function setDescripcionProducto(string $descripcion_producto): self
    {
        $this->descripcion_producto = $descripcion_producto;

        return $this;
    }

    public function getCategoriaProducto(): ?string
    {
        return $this->categoria_producto;
    }

    public function setCategoriaProducto(string $categoria_producto): self
    {
        $this->categoria_producto = $categoria_producto;

        return $this;
    }

    public function getNetContent(): ?string
    {
        return $this->net_content;
    }

    public function setNetContent(string $net_content): self
    {
        $this->net_content = $net_content;

        return $this;
    }

    public function getCompaniaProducto(): ?string
    {
        return $this->compania_producto;
    }

    public function setCompaniaProducto(string $compania_producto): self
    {
        $this->compania_producto = $compania_producto;

        return $this;
    }

    public function getEstadoProducto(): ?bool
    {
        return $this->estado_producto;
    }

    public function setEstadoProducto(bool $estado_producto): self
    {
        $this->estado_producto = $estado_producto;

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

    public function getPais(): ?Pais
    {
        return $this->pais;
    }

    public function setPais(?Pais $pais): self
    {
        $this->pais = $pais;

        return $this;
    }
}
