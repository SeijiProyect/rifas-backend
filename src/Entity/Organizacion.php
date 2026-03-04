<?php

namespace App\Entity;

use App\Repository\OrganizacionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrganizacionRepository::class)
 */
class Organizacion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $rut;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $razonSocial;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $direccion;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $telefono;

    /**
     * @ORM\OneToMany(targetEntity=Rifa::class, mappedBy="organizacion", orphanRemoval=true)
     */
    private $rifas;

    public function __construct()
    {
        $this->rifas = new ArrayCollection();
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

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(?string $rut): self
    {
        $this->rut = $rut;

        return $this;
    }

    public function getRazonSocial(): ?string
    {
        return $this->razonSocial;
    }

    public function setRazonSocial(?string $razonSocial): self
    {
        $this->razonSocial = $razonSocial;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * @return Collection|Rifa[]
     */
    public function getRifas(): Collection
    {
        return $this->rifas;
    }

    public function addRifa(Rifa $rifa): self
    {
        if (!$this->rifas->contains($rifa)) {
            $this->rifas[] = $rifa;
            $rifa->setOrganizacion($this);
        }

        return $this;
    }

    public function removeRifa(Rifa $rifa): self
    {
        if ($this->rifas->removeElement($rifa)) {
            // set the owning side to null (unless already changed)
            if ($rifa->getOrganizacion() === $this) {
                $rifa->setOrganizacion(null);
            }
        }

        return $this;
    }
}
