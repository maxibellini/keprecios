<?php

namespace App\Entity;

use App\Repository\CuponRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CuponRepository::class)
 */
class Cupon
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCreacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaVto;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $estado;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaUso;

    /**
     * @ORM\ManyToOne(targetEntity=Voucher::class, inversedBy="cupones")
     */
    private $voucher;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cupones")
     */
    private $user;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nroCupon;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $semilla;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFechaVto(): ?\DateTimeInterface
    {
        return $this->fechaVto;
    }

    public function setFechaVto(?\DateTimeInterface $fechaVto): self
    {
        $this->fechaVto = $fechaVto;

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

    public function getFechaUso(): ?\DateTimeInterface
    {
        return $this->fechaUso;
    }

    public function setFechaUso(?\DateTimeInterface $fechaUso): self
    {
        $this->fechaUso = $fechaUso;

        return $this;
    }

    public function getVoucher(): ?Voucher
    {
        return $this->voucher;
    }

    public function setVoucher(?Voucher $voucher): self
    {
        $this->voucher = $voucher;

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

    public function getNroCupon(): ?int
    {
        return $this->nroCupon;
    }

    public function setNroCupon(?int $nroCupon): self
    {
        $this->nroCupon = $nroCupon;

        return $this;
    }

    public function getSemilla(): ?int
    {
        return $this->semilla;
    }

    public function setSemilla(?int $semilla): self
    {
        $this->semilla = $semilla;

        return $this;
    }
    public function __toString()
    {
         return $this->id.'-'.$this->nroCupon.'-'.$this->semilla;
       
    }
}
