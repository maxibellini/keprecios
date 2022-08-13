<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\EntityManagerInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @Vich\Uploadable
 */
class User implements UserInterface , \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\ManyToOne(targetEntity=Localidad::class, inversedBy="users")
     */
    private $localidadDomicilio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombrePersona;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apellidoPersona;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $telefono;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $telefonoAlternativo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $domicilio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $codigoPostal;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $sexo;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ultimaConexion;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitud;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitud;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaNacimiento;


    private $em;
    private $users;

   /**
     * @ORM\Column(type="string", length=255, nullable =true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="usuarios_images", fileNameProperty="image")
     * @Ignore()
     */
    private $imageFile ;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Producto::class, mappedBy="user")
     */
    private $productos;

    /**
     * @ORM\OneToMany(targetEntity=Comercio::class, mappedBy="user")
     */
    private $comercio;

    /**
     * @ORM\OneToMany(targetEntity=Oferta::class, mappedBy="user")
     */
    private $ofertas;

    public function __construct()
    {
        $this->productos = new ArrayCollection();
        $this->comercio = new ArrayCollection();
        $this->ofertas = new ArrayCollection();
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
    public function getUsers(): ?Array
    {
        return $this->users;
    }

    public function setUsers(?Array $users): self
    {
        $this->users = $users;

        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLocalidadDomicilio(): ?Localidad
    {
        return $this->localidadDomicilio;
    }

    public function setLocalidadDomicilio(?Localidad $localidadDomicilio): self
    {
        $this->localidadDomicilio = $localidadDomicilio;

        return $this;
    }
    public function getNombrePersona(): ?string
    {
        return $this->nombrePersona;
    }

    public function setNombrePersona(string $nombrePersona): self
    {
        $this->nombrePersona = $nombrePersona;

        return $this;
    }

    public function getApellidoPersona(): ?string
    {
        return $this->apellidoPersona;
    }

    public function setApellidoPersona(string $apellidoPersona): self
    {
        $this->apellidoPersona = $apellidoPersona;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getTelefonoAlternativo(): ?string
    {
        return $this->telefonoAlternativo;
    }

    public function setTelefonoAlternativo(string $telefonoAlternativo): self
    {
        $this->telefonoAlternativo = $telefonoAlternativo;

        return $this;
    }

    public function getDomicilio(): ?string
    {
        return $this->domicilio;
    }

    public function setDomicilio(string $domicilio): self
    {
        $this->domicilio = $domicilio;

        return $this;
    }

    public function getCodigoPostal(): ?string
    {
        return $this->codigoPostal;
    }

    public function setCodigoPostal(string $codigoPostal): self
    {
        $this->codigoPostal = $codigoPostal;

        return $this;
    }

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(string $sexo): self
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(?\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getUltimaConexion(): ?\DateTimeInterface
    {
        return $this->ultimaConexion;
    }

    public function setUltimaConexion(?\DateTimeInterface $ultimaConexion): self
    {
        $this->ultimaConexion = $ultimaConexion;

        return $this;
    }

    public function getLatitud(): ?float
    {
        return $this->latitud;
    }

    public function setLatitud(?float $latitud): self
    {
        $this->latitud = $latitud;

        return $this;
    }

    public function getLongitud(): ?float
    {
        return $this->longitud;
    }

    public function setLongitud(?float $longitud): self
    {
        $this->longitud = $longitud;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento(?\DateTimeInterface $fechaNacimiento): self
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }


    public function __toString()
    {
       return $this->name;
    }
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->name,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->name,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $contNames=0; $contEmails=0;
        //setea nombres
        $this->setNombrePersona( ucwords(strtolower($this->getNombrePersona())));
        $this->setApellidoPersona( strtoupper($this->getApellidoPersona()) );

        if ($this->em != null){
            $nameusuario= $this->getName();
            $emailusuario= $this->getEmail();
            $usersn= $this->em->getRepository("App:User")->findOneBy(array('name'=>$nameusuario));
            $userse= $this->em->getRepository("App:User")->findOneBy(array('email'=>$emailusuario));
            if($usersn){
              if($usersn->getId() != $this->getId()) {
                $context->buildViolation('Error: El nombre de usuario ya esta en uso')
                    ->atPath('')
                    ->addViolation();
              }
            }
            if($userse){
              if($userse->getId() != $this->getId()) {
                $context->buildViolation('Error: El correo electronico ya esta en uso')
                    ->atPath('')
                    ->addViolation();
              }
            }
        } 

        $usuarios=$this->users; 
        if (is_array($usuarios)){
            foreach ($usuarios as $usuario) {
               if($usuario->getName() == $this->getName() ){
                  $contNames++;
               }
               if($usuario->getEmail() == $this->getEmail() ){
                  $contEmails++;
               }
            }
        }
        if( $contNames > 1 ){
            $context->buildViolation('Error: El nombre de usuario ya esta en uso')
                ->atPath('')
                ->addViolation();
        }
        if( $contEmails > 1 ){
            $context->buildViolation('Error: El correo electronico ya esta en uso')
                ->atPath('')
                ->addViolation();
        }


    }

    /**
     * @return Collection<int, Producto>
     */
    public function getProductos(): Collection
    {
        return $this->productos;
    }

    public function addProducto(Producto $producto): self
    {
        if (!$this->productos->contains($producto)) {
            $this->productos[] = $producto;
            $producto->setUser($this);
        }

        return $this;
    }

    public function removeProducto(Producto $producto): self
    {
        if ($this->productos->removeElement($producto)) {
            // set the owning side to null (unless already changed)
            if ($producto->getUser() === $this) {
                $producto->setUser(null);
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
            $comercio->setUser($this);
        }

        return $this;
    }

    public function removeComercio(Comercio $comercio): self
    {
        if ($this->comercio->removeElement($comercio)) {
            // set the owning side to null (unless already changed)
            if ($comercio->getUser() === $this) {
                $comercio->setUser(null);
            }
        }

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
            $oferta->setUser($this);
        }

        return $this;
    }

    public function removeOferta(Oferta $oferta): self
    {
        if ($this->ofertas->removeElement($oferta)) {
            // set the owning side to null (unless already changed)
            if ($oferta->getUser() === $this) {
                $oferta->setUser(null);
            }
        }

        return $this;
    }



}
