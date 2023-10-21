<?php

namespace App\Entity;

use App\Repository\ComercioRepository;
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
 * @ORM\Entity(repositoryClass=ComercioRepository::class)
 * @Vich\Uploadable
 */
class Comercio
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $cuitComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombreComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $direccionComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $emailComercio;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaRegistroComercio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $horaAperturaComercio;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitudComercio;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitudComercio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $horaCierreComercio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $estadoComercio;

    /**
     * @ORM\Column(type="string", length=800)
     */
    private $descripcionComercio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telefonoComercio;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sucursal;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="Comercio")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Localidad::class, inversedBy="comercios")
     */
    private $localidad;

    /**
     * @ORM\OneToMany(targetEntity=Oferta::class, mappedBy="comercio")
     */
    private $oferta;

   /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="comercios_images", fileNameProperty="image")
     * @Ignore()
     */
    private $imageFile ;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;
    private $em;
    private $comercios;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motivoRechazo;

    /**
     * @ORM\OneToMany(targetEntity=Colaboracion::class, mappedBy="comercio",cascade={"persist"})
     */
    private $colaboracions;

    /**
     * @ORM\ManyToOne(targetEntity=Confianza::class, inversedBy="comercio")
     */
    private $confianza;

    public function __construct()
    {
        $this->oferta = new ArrayCollection();
        $this->colaboracions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuitComercio(): ?int
    {
        return $this->cuitComercio;
    }

    public function setCuitComercio(int $cuitComercio): self
    {
        $this->cuitComercio = $cuitComercio;

        return $this;
    }

    public function getNombreComercio(): ?string
    {
        return $this->nombreComercio;
    }

    public function setNombreComercio(string $nombreComercio): self
    {
        $this->nombreComercio = $nombreComercio;

        return $this;
    }

    public function getDireccionComercio(): ?string
    {
        return $this->direccionComercio;
    }

    public function setDireccionComercio(string $direccionComercio): self
    {
        $this->direccionComercio = $direccionComercio;

        return $this;
    }

    public function getEmailComercio(): ?string
    {
        return $this->emailComercio;
    }

    public function setEmailComercio(string $emailComercio): self
    {
        $this->emailComercio = $emailComercio;

        return $this;
    }

    public function getFechaRegistroComercio(): ?\DateTimeInterface
    {
        return $this->fechaRegistroComercio;
    }

    public function setFechaRegistroComercio(\DateTimeInterface $fechaRegistroComercio): self
    {
        $this->fechaRegistroComercio = $fechaRegistroComercio;

        return $this;
    }

    public function getHoraAperturaComercio(): ?\DateTimeInterface
    {
        return $this->horaAperturaComercio;
    }

    public function setHoraAperturaComercio(?\DateTimeInterface $horaAperturaComercio): self
    {
        $this->horaAperturaComercio = $horaAperturaComercio;

        return $this;
    }

    public function getLatitudComercio(): ?float
    {
        return $this->latitudComercio;
    }

    public function setLatitudComercio(float $latitudComercio): self
    {
        $this->latitudComercio = $latitudComercio;

        return $this;
    }

    public function getLongitudComercio(): ?float
    {
        return $this->longitudComercio;
    }

    public function setLongitudComercio(float $longitudComercio): self
    {
        $this->longitudComercio = $longitudComercio;

        return $this;
    }

    public function getHoraCierreComercio(): ?\DateTimeInterface
    {
        return $this->horaCierreComercio;
    }

    public function setHoraCierreComercio(?\DateTimeInterface $horaCierreComercio): self
    {
        $this->horaCierreComercio = $horaCierreComercio;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getEstadoComercio(): ?string
    {
        return $this->estadoComercio;
    }

    public function setEstadoComercio(string $estadoComercio): self
    {
        $this->estadoComercio = $estadoComercio;

        return $this;
    }

    public function getDescripcionComercio(): ?string
    {
        return $this->descripcionComercio;
    }

    public function setDescripcionComercio(string $descripcionComercio): self
    {
        $this->descripcionComercio = $descripcionComercio;

        return $this;
    }

    public function getTelefonoComercio(): ?string
    {
        return $this->telefonoComercio;
    }

    public function setTelefonoComercio(string $telefonoComercio): self
    {
        $this->telefonoComercio = $telefonoComercio;

        return $this;
    }

    public function getSucursal(): ?bool
    {
        return $this->sucursal;
    }

    public function setSucursal(bool $sucursal): self
    {
        $this->sucursal = $sucursal;

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

    public function getLocalidad(): ?Localidad
    {
        return $this->localidad;
    }

    public function setLocalidad(?Localidad $localidad): self
    {
        $this->localidad = $localidad;

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
            $ofertum->setComercio($this);
        }

        return $this;
    }

    public function removeOfertum(Oferta $ofertum): self
    {
        if ($this->oferta->removeElement($ofertum)) {
            // set the owning side to null (unless already changed)
            if ($ofertum->getComercio() === $this) {
                $ofertum->setComercio(null);
            }
        }

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
    public function getComercios(): ?Array
    {
        return $this->comercios;
    }

    public function setComercios(?Array $comercios): self
    {
        $this->comercios = $comercios;

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
    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $contNames=0; $contEmails=0; $contCuits=0; $contLtLn=0;
        //setea nombre
        $this->setNombreComercio( ucwords(strtolower($this->getNombreComercio())));
        if($this->getCuitComercio()!= NULL){
            if ( $this->getCuitComercio() < 0 or $this->getCuitComercio() > 99999999999 ){
                $context->buildViolation('Error: El CUIT ingresado esta fuera de rango.')
                    ->atPath('')
                    ->addViolation();   
            } 
        }
        if($this->getLatitudComercio() != null) {
            if ($this->getLatitudComercio() > 90 or  $this->getLatitudComercio() < -90 ){
                $context->buildViolation('Error: La latitud esta fuera de rango, debería estar entre -90 y 90.')
                     ->atPath('')
                     ->addViolation();
            }
        }
        if($this->getLongitudComercio() != null) {
            if ($this->getLongitudComercio() > 90 or  $this->getLongitudComercio() < -90 ){
                $context->buildViolation('Error: La longitud esta fuera de rango, debería estar entre -90 y 90.')
                     ->atPath('')
                     ->addViolation();
            }
        }
        if ($this->em != null){
            $namecomercio= $this->getNombreComercio();
            $emailcomercio= $this->getEmailComercio();
            $cuitcomercio= $this->getCuitComercio();
            $latcomercio= $this->getLatitudComercio();
            $longcomercio= $this->getLongitudComercio();
            $comercion= $this->em->getRepository("App:Comercio")->findOneBy(array('nombreComercio'=>$namecomercio));
            $comercioe= $this->em->getRepository("App:Comercio")->findOneBy(array('emailComercio'=>$emailcomercio));
            $comercioc= $this->em->getRepository("App:Comercio")->findOneBy(array('cuitComercio'=>$cuitcomercio));
            $comercioltln= $this->em->getRepository("App:Comercio")->findOneBy(array('latitudComercio'=>$latcomercio, 'longitudComercio'=>$longcomercio));
    
            if($comercion){
              if($comercion->getId() != $this->getId()) {
                $context->buildViolation('Error: El nombre de comercio ya está en uso')
                    ->atPath('')
                    ->addViolation();
              }
            }
            if($comercioe){
              if($comercioe->getId() != $this->getId()) {
                $context->buildViolation('Error: El correo electrónico ya está en uso')
                    ->atPath('')
                    ->addViolation();
              }
            }
            if($comercioc and $this->getSucursal() == 0 ){
              if($comercioc->getId() != $this->getId()) {
                $context->buildViolation('Error: El cuit del comercio ya está en uso')
                    ->atPath('')
                    ->addViolation();
              }
            }
            if($comercioltln ){
              if($comercioc->getId() != $this->getId()) {
                $context->buildViolation('Error: Esa ubicación ya está en uso')
                    ->atPath('')
                    ->addViolation();
              }
            }
        } 

        $comercios=$this->comercios; 
        if (is_array($comercios)){
            foreach ($comercios as $comercio) {
               if($comercio->getNombreComercio() == $this->getNombreComercio() ){
                  $contNames++;
               }
               if($comercio->getEmailComercio() == $this->getEmailComercio() ){
                  $contEmails++;
               }
               if($comercio->getCuitComercio() == $this->getCuitComercio() and $this->getSucursal() == 0 ){
                  $contCuits++;
               }
               if($comercio->getLatitudComercio() == $this->getLatitudComercio() and $comercio->getLongitudComercio() == $this->getLongitudComercio()  ){
                  $contLtLn++;
               }
            }
        }
        if( $contNames > 1 ){
            $context->buildViolation('Error: El nombre de comercio ya está en uso')
                ->atPath('')
                ->addViolation();
        }
        if( $contEmails > 1 ){
            $context->buildViolation('Error: El correo electrónico ya está en uso')
                ->atPath('')
                ->addViolation();
        }
        if( $contCuits > 1 ){
            $context->buildViolation('Error: El cuit del comercio ya está en uso')
                ->atPath('')
                ->addViolation();
        }
        if( $contLtLn > 1 ){
            $context->buildViolation('Error: Esa ubicación ya está en uso')
                ->atPath('')
                ->addViolation();
        }
    }

    public function getMotivoRechazo(): ?string
    {
        return $this->motivoRechazo;
    }

    public function setMotivoRechazo(?string $motivoRechazo): self
    {
        $this->motivoRechazo = $motivoRechazo;

        return $this;
    }
    public function __toString()
    {
        if ($this->localidad != null){
         return $this->cuitComercio.' - '.$this->nombreComercio.' - '.$this->direccionComercio.' - '.$this->localidad->getNombre();
        }else{
            return $this->cuitComercio.' - '.$this->nombreComercio.' - '.$this->direccionComercio;
        }
       
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
            $colaboracion->setComercio($this);
        }

        return $this;
    }

    public function removeColaboracion(Colaboracion $colaboracion): self
    {
        if ($this->colaboracions->removeElement($colaboracion)) {
            // set the owning side to null (unless already changed)
            if ($colaboracion->getComercio() === $this) {
                $colaboracion->setComercio(null);
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
