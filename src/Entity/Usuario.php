<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsuarioRepository::class)
 */
class Usuario
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
    private $nombre_persona;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $apellido_persona;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $telefono;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $telefono_alternativo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $domicilio;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $codigo_postal;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $sexo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombrePersona(): ?string
    {
        return $this->nombre_persona;
    }

    public function setNombrePersona(string $nombre_persona): self
    {
        $this->nombre_persona = $nombre_persona;

        return $this;
    }

    public function getApellidoPersona(): ?string
    {
        return $this->apellido_persona;
    }

    public function setApellidoPersona(string $apellido_persona): self
    {
        $this->apellido_persona = $apellido_persona;

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
        return $this->telefono_alternativo;
    }

    public function setTelefonoAlternativo(string $telefono_alternativo): self
    {
        $this->telefono_alternativo = $telefono_alternativo;

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
        return $this->codigo_postal;
    }

    public function setCodigoPostal(string $codigo_postal): self
    {
        $this->codigo_postal = $codigo_postal;

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
}
