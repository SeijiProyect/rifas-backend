<?php

namespace App\Entity;

use App\Repository\RifaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RifaRepository::class)
 */
class Rifa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaInicio;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaFin;

    /**
     * @ORM\ManyToOne(targetEntity=Organizacion::class, inversedBy="rifas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organizacion;

    /**
     * @ORM\OneToMany(targetEntity=Sorteo::class, mappedBy="rifa", orphanRemoval=true)
     */
    private $sorteos;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activa;

    public function __construct()
    {
        $this->sorteos = new ArrayCollection();
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(?\DateTimeInterface $fechaInicio): self
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fechaFin;
    }

    public function setFechaFin(?\DateTimeInterface $fechaFin): self
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getOrganizacion(): ?Organizacion
    {
        return $this->organizacion;
    }

    public function setOrganizacion(?Organizacion $organizacion): self
    {
        $this->organizacion = $organizacion;

        return $this;
    }

    /**
     * @return Collection|Sorteo[]
     */
    public function getSorteos(): Collection
    {
        return $this->sorteos;
    }

    public function addSorteo(Sorteo $sorteo): self
    {
        if (!$this->sorteos->contains($sorteo)) {
            $this->sorteos[] = $sorteo;
            $sorteo->setRifa($this);
        }

        return $this;
    }

    public function removeSorteo(Sorteo $sorteo): self
    {
        if ($this->sorteos->removeElement($sorteo)) {
            // set the owning side to null (unless already changed)
            if ($sorteo->getRifa() === $this) {
                $sorteo->setRifa(null);
            }
        }

        return $this;
    }

    public function getActiva(): ?bool
    {
        return $this->activa;
    }

    public function setActiva(bool $activa): self
    {
        $this->activa = $activa;

        return $this;
    }
}
