<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityManagerInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=ProductoRepository::class)
 * @Vich\Uploadable
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
    private $marcaProducto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcionProducto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categoriaProducto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $netContent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $companiaProducto;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estadoProducto;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="productos")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Pais::class, inversedBy="productos")
     */
    private $pais;

    /**
     * @ORM\OneToMany(targetEntity=Oferta::class, mappedBy="producto")
     */
    private $ofertas;

   /**
     * @ORM\Column(type="string", length=255, nullable =true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="productos_images", fileNameProperty="image")
     * @Ignore()
     */
    private $imageFile ;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;
    private $em;
    private $productos;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imgUrl;

    /**
     * @ORM\OneToMany(targetEntity=Colaboracion::class, mappedBy="producto",cascade={"persist"})
     */
    private $colaboracions;

    /**
     * @ORM\ManyToOne(targetEntity=Confianza::class, inversedBy="producto")
     */
    private $confianza;


    public function __construct()
    {
        $this->ofertas = new ArrayCollection();
        $this->colaboracions = new ArrayCollection();
    }

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
        return $this->marcaProducto;
    }

    public function setMarcaProducto(string $marcaProducto): self
    {
        $this->marcaProducto = $marcaProducto;

        return $this;
    }

    public function getDescripcionProducto(): ?string
    {
        return $this->descripcionProducto;
    }

    public function setDescripcionProducto(string $descripcionProducto): self
    {
        $this->descripcionProducto = $descripcionProducto;

        return $this;
    }

    public function getCategoriaProducto(): ?string
    {
        return $this->categoriaProducto;
    }

    public function setCategoriaProducto(string $categoriaProducto): self
    {
        $this->categoriaProducto = $categoriaProducto;

        return $this;
    }

    public function getNetContent(): ?string
    {
        return $this->netContent;
    }

    public function setNetContent(string $netContent): self
    {
        $this->netContent = $netContent;

        return $this;
    }

    public function getCompaniaProducto(): ?string
    {
        return $this->companiaProducto;
    }

    public function setCompaniaProducto(string $companiaProducto): self
    {
        $this->companiaProducto = $companiaProducto;

        return $this;
    }

    public function getEstadoProducto(): ?bool
    {
        return $this->estadoProducto;
    }

    public function setEstadoProducto(bool $estadoProducto): self
    {
        $this->estadoProducto = $estadoProducto;

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

    /**
     * @return Collection<int, Oferta>
     */
    public function getOfertas(): Collection
    {
        return $this->ofertas;
    }

    public function addOferta(Oferta $oferta): self
    {
        if (!$this->ofertas->contains($oferta)) {
            $this->ofertas[] = $oferta;
            $oferta->setProducto($this);
        }

        return $this;
    }

    public function removeOferta(Oferta $oferta): self
    {
        if ($this->ofertas->removeElement($oferta)) {
            // set the owning side to null (unless already changed)
            if ($oferta->getProducto() === $this) {
                $oferta->setProducto(null);
            }
        }

        return $this;
    }

    public function setImageFile( $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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
    public function getProductos(): ?Array
    {
        return $this->productos;
    }

    public function setProductos(?Array $productos): self
    {
        $this->productos = $productos;

        return $this;
    }

    public function getImgUrl(): ?string
    {
        return $this->imgUrl;
    }

    public function setImgUrl(?string $imgUrl): self
    {
        $this->imgUrl = $imgUrl;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $contcodigos=0;
        if ($this->em != null){
            $gtin= $this->getGtin();
            $productogt= $this->em->getRepository("App:Producto")->findOneBy(array('gtin'=>$gtin));  
            if($productogt){
              if($productogt->getId() != $this->getId()) {
                $context->buildViolation('Error: El código de producto ya está en uso')
                    ->atPath('')
                    ->addViolation();
              }
            }

        } 
        $productos=$this->productos; 
        if (is_array($productos)){
            foreach ($productos as $producto) {
               if($producto->getGtin() == $this->getGtin() ){
                  $contcodigos++;
               }
            }
        }
        if( $contcodigos > 1 ){
            $context->buildViolation('Error: El código de producto ya está en uso')
                ->atPath('')
                ->addViolation();
        }
        if( $this->getCategoriaProducto() == '' ){
            $context->buildViolation('Error: Debe ingresar una categoría')
                ->atPath('')
                ->addViolation();
        }
        //dd($context);

    }
    public function __toString()
    {
        return $this->gtin.' - '.$this->descripcionProducto.' - '.$this->marcaProducto.' - '.$this->netContent;
    }

    /**
     * @return Collection<int, Colaboracion>
     */
    public function getColaboracions(): Collection
    {
        return $this->colaboracions;
    }

    public function addColaboracion(Colaboracion $colaboracion): self
    {
        if (!$this->colaboracions->contains($colaboracion)) {
            $this->colaboracions[] = $colaboracion;
            $colaboracion->setProducto($this);
        }

        return $this;
    }

    public function removeColaboracion(Colaboracion $colaboracion): self
    {
        if ($this->colaboracions->removeElement($colaboracion)) {
            // set the owning side to null (unless already changed)
            if ($colaboracion->getProducto() === $this) {
                $colaboracion->setProducto(null);
            }
        }

        return $this;
    }

    public function getConfianza(): ?Confianza
    {
        return $this->confianza;
    }

    public function setConfianza(?Confianza $confianza): self
    {
        $this->confianza = $confianza;

        return $this;
    }

}
