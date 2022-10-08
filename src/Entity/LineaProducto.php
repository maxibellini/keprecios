<?php

namespace App\Entity;

use App\Repository\LineaProductoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LineaProductoRepository::class)
 */
class LineaProducto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Producto::class)
     */
    private $producto;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity=ListaCompra::class, inversedBy="lineasProductos")
     */
    private $listaCompra;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(?int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getListaCompra(): ?ListaCompra
    {
        return $this->listaCompra;
    }

    public function setListaCompra(?ListaCompra $listaCompra): self
    {
        $this->listaCompra = $listaCompra;

        return $this;
    }
}
