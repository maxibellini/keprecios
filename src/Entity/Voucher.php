<?php

namespace App\Entity;

use App\Repository\VoucherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=VoucherRepository::class)
 * @Vich\Uploadable
 */
class Voucher
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
    private $nombre;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaCreacion;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="integer")
     */
    private $costo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duracion;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="vouchers")
     */
    private $responsable;

    /**
     * @ORM\OneToMany(targetEntity=Cupon::class, mappedBy="voucher",cascade={"persist"})
     */
    private $cupones;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entidad;

   /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="vouchers_images", fileNameProperty="image")
     * @Ignore()
     */
    private $imageFile ;

    public function __construct()
    {
        $this->cupones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

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

    public function getCosto(): ?int
    {
        return $this->costo;
    }

    public function setCosto(int $costo): self
    {
        $this->costo = $costo;

        return $this;
    }

    public function getDuracion(): ?int
    {
        return $this->duracion;
    }

    public function setDuracion(?int $duracion): self
    {
        $this->duracion = $duracion;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): self
    {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * @return Collection<int, Cupon>
     */
    public function getCupones(): Collection
    {
        return $this->cupones;
    }

    public function addCupone(Cupon $cupone): self
    {
        if (!$this->cupones->contains($cupone)) {
            $this->cupones[] = $cupone;
            $cupone->setVoucher($this);
        }

        return $this;
    }

    public function removeCupone(Cupon $cupone): self
    {
        if ($this->cupones->removeElement($cupone)) {
            // set the owning side to null (unless already changed)
            if ($cupone->getVoucher() === $this) {
                $cupone->setVoucher(null);
            }
        }

        return $this;
    }

    public function getEntidad(): ?string
    {
        return $this->entidad;
    }

    public function setEntidad(?string $entidad): self
    {
        $this->entidad = $entidad;

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
    public function __toString()
    {
         return $this->entidad.' - '.$this->nombre;
       
    }
   /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
     
        if( $this->responsable == null ){
            $context->buildViolation('Error: Debe asignar un responsable')
                ->atPath('')
                ->addViolation();
        }
        if( $this->costo == null ){
            $context->buildViolation('Error: Debe establecer un costo')
                ->atPath('')
                ->addViolation();
        }
        if( $this->costo == 0 ){
            $context->buildViolation('Error: El costo debe ser mayor a 0')
                ->atPath('')
                ->addViolation();
        }
        if( $this->fechaCreacion == null ){
            $hoy= new \DateTime();
            $this->setFechaCreacion($hoy);
        }        
        if( $this->duracion == null ){
            $context->buildViolation('Error: Debe establecer una cantidad stock de cupones')
                ->atPath('')
                ->addViolation();
        }
        if( $this->estado == null ){
            $context->buildViolation('Error: Debe establecer un estado del voucher')
                ->atPath('')
                ->addViolation();
        }
        if( $this->entidad == null ){
            $context->buildViolation('Error: Debe establecer una entidad del voucher')
                ->atPath('')
                ->addViolation();
        }
    }

}
