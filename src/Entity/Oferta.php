<?php

namespace App\Entity;

use App\Repository\OfertaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityManagerInterface;

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
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $monto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcionOferta;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tipoDescuento;

    /**
     * @ORM\Column(type="string")
     */
    private $stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity=Comercio::class, inversedBy="oferta")
     */
    private $comercio;

    /**
     * @ORM\ManyToOne(targetEntity=Producto::class, inversedBy="ofertas",cascade={"persist"})
     */
    private $producto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motivoBaja;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ofertas")
     */
    private $user;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaCarga;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaUpdate;

    private $em;
    private $ofertas;

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
        return $this->descripcionOferta;
    }

    public function setDescripcionOferta(string $descripcionOferta): self
    {
        $this->descripcionOferta = $descripcionOferta;

        return $this;
    }

    public function getTipoDescuento(): ?string
    {
        return $this->tipoDescuento;
    }

    public function setTipoDescuento(string $tipoDescuento): self
    {
        $this->tipoDescuento = $tipoDescuento;

        return $this;
    }

    public function getStock(): ?string
    {
        return $this->stock;
    }

    public function setStock(string $stock): self
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

    public function getComercio(): ?Comercio
    {
        return $this->comercio;
    }

    public function setComercio(?Comercio $comercio): self
    {
        $this->comercio = $comercio;

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

    public function getMotivoBaja(): ?string
    {
        return $this->motivoBaja;
    }

    public function setMotivoBaja(?string $motivoBaja): self
    {
        $this->motivoBaja = $motivoBaja;

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

    public function getFechaCarga(): ?\DateTimeInterface
    {
        return $this->fechaCarga;
    }

    public function setFechaCarga(?\DateTimeInterface $fechaCarga): self
    {
        $this->fechaCarga = $fechaCarga;

        return $this;
    }

    public function getFechaUpdate(): ?\DateTimeInterface
    {
        return $this->fechaUpdate;
    }

    public function setFechaUpdate(?\DateTimeInterface $fechaUpdate): self
    {
        $this->fechaUpdate = $fechaUpdate;

        return $this;
    }
    public function getEm(): ?EntityManagerInterface
    {
        return $this->em;
    }

    public function setEm(?EntityManagerInterface $em): self
    {
        $this->em = $em;

        return $this;
    }
    public function getOfertas(): ?Array
    {
        return $this->ofertas;
    }

    public function setOfertas(?Array $ofertas): self
    {
        $this->ofertas = $ofertas;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        
        if($this->getProducto() == null){
                $context->buildViolation('Error: Debe ingresar un producto')
                    ->atPath('')
                    ->addViolation();
        }
        if($this->getComercio() == null){
                $context->buildViolation('Error: Debe ingresar el comercio')
                    ->atPath('')
                    ->addViolation();
        }
        $contofertas=0;
        if ($this->em != null){
            $prod= $this->getProducto();
            $comerc= $this->getComercio();
            $ofertaprod= $this->em->getRepository("App:Oferta")->findOneBy(array('producto'=>$prod,
                                                                                 'comercio' => $comerc,
                                                                                 'estado' => 1)); 

            if($ofertaprod){
                $context->buildViolation('Error: Ya existe una oferta activa de este producto en este comercio.')
                    ->atPath('')
                    ->addViolation();
            }

        } 
        $ofertas=$this->ofertas; 
        if (is_array($ofertas)){
            foreach ($ofertas as $oferta) {
               if($oferta->getProducto() == $this->getProducto() and $oferta->getComercio() == $this->getComercio() and $oferta->getEstado() == 1 ){
                  $contofertas++;
               }
            }
        }
        if( $contofertas > 1 ){
            $context->buildViolation('Error: Ya existe una oferta activa de este producto en este comercio.')
                ->atPath('')
                ->addViolation();
        }
        if($this->getStock() == 'Sin stock'){
            $this->setEstado(0);
        }
        //dd($context);
    }

    public function __toString()
    {
        return $this->monto.' - '.$this->descripcionOferta;
    }
}
