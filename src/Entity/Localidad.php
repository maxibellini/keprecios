<?php

namespace App\Entity;

use App\Repository\LocalidadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LocalidadRepository::class)
 */
class Localidad
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="localidadDomicilio")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=Provincia::class, inversedBy="localidades")
     */
    private $provincia;

    /**
     * @ORM\OneToMany(targetEntity=Comercio::class, mappedBy="localidad")
     */
    private $comercios;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->comercios = new ArrayCollection();
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

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(?bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setLocalidadDomicilio($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getLocalidadDomicilio() === $this) {
                $user->setLocalidadDomicilio(null);
            }
        }

        return $this;
    }

    public function getProvincia(): ?Provincia
    {
        return $this->provincia;
    }

    public function setProvincia(?Provincia $provincia): self
    {
        $this->provincia = $provincia;

        return $this;
    }
    public function __toString()
    {
        if($this->getProvincia() != null){
           if($this->getProvincia()->getPais() != null){
               return $this->getProvincia()->getPais()->getNombre().' - '.$this->getProvincia()->getNombre().' - '.$this->nombre;
           }else{
                return 'S/N - '.$this->getProvincia()->getNombre().' - '.$this->nombre;
           } 
        }else{
            return 'S/N - S/N - '.$this->nombre;
        }
       
    }

    /**
     * @return Collection<int, Comercio>
     */
    public function getComercios(): Collection
    {
        return $this->comercios;
    }

    public function addComercio(Comercio $comercio): self
    {
        if (!$this->comercios->contains($comercio)) {
            $this->comercios[] = $comercio;
            $comercio->setLocalidad($this);
        }

        return $this;
    }

    public function removeComercio(Comercio $comercio): self
    {
        if ($this->comercios->removeElement($comercio)) {
            // set the owning side to null (unless already changed)
            if ($comercio->getLocalidad() === $this) {
                $comercio->setLocalidad(null);
            }
        }

        return $this;
    }

}
