<?php

namespace App\Entity;

use App\Repository\ListaCompraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @ORM\Entity(repositoryClass=ListaCompraRepository::class)
 */
class ListaCompra
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombre;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="listaCompras")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=LineaProducto::class, mappedBy="listaCompra", cascade={"persist","remove"})
     */
    private $lineasProductos;

    public function __construct()
    {
        $this->lineasProductos = new ArrayCollection();
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

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(?\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

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

    /**
     * @return Collection<int, LineaProducto>
     */
    public function getLineasProductos(): Collection
    {
        return $this->lineasProductos;
    }

    public function addLineasProducto(LineaProducto $lineasProducto): self
    {
        if (!$this->lineasProductos->contains($lineasProducto)) {
            $this->lineasProductos[] = $lineasProducto;
            $lineasProducto->setListaCompra($this);
        }

        return $this;
    }

    public function removeLineasProducto(LineaProducto $lineasProducto): self
    {
        if ($this->lineasProductos->removeElement($lineasProducto)) {
            // set the owning side to null (unless already changed)
            if ($lineasProducto->getListaCompra() === $this) {
                $lineasProducto->setListaCompra(null);
            }
        }

        return $this;
    }
    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $usuario = $this->getUser();
        $nombre = $this->getNombre();
        $contRep= 0;
        $repetido = false;
        if($nombre == ''){
            $context->buildViolation('Error: Ingrese un nombre para la Lista')
                ->atPath('')
                ->addViolation();
        }
        if($usuario != null){
           $listas= $usuario->getListaCompras();
           if(count($listas) > 0 ){
             foreach ($listas as $lista) {
                 if($lista->getNombre() == $nombre){
                    if($this->getId() != $lista->getId()){
                        $context->buildViolation('Error: Ya tiene una lista con ese nombre')
                            ->atPath('')
                            ->addViolation();
                    }
                 }
             }
           }
        }
        $lproductos= $this->getLineasProductos();
           if(count($lproductos) < 2 ){
                    $context->buildViolation('Error: Debe cargar al menos 2 productos en la Lista.')
                        ->atPath('')
                        ->addViolation();
           }else{
             $cantidad=0;
             foreach ($lproductos as $lproducto) {
                foreach ($lproductos as $lprod) {
                   if($lproducto->getProducto() == $lprod->getProducto() ){
                      $contRep++;
                   }
                }
                if($contRep > 1){
                  $repetido= true;
                }
                $contRep=0;
                if($lproducto->getCantidad() < 1){
                   $cantidad++;
                }
             }
             if($repetido == true){
                $context->buildViolation('Error: Tiene productos repetidos en su lista de compras, controle por favor')
                            ->atPath('')
                            ->addViolation();
             }
             if($cantidad > 0){
                $context->buildViolation('Error: Controle las cantidades, recuerde que deben ser mayor a 0')
                            ->atPath('')
                            ->addViolation();
             }
           }        
        
    }
}
