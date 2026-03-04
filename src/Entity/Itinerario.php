<?php

namespace App\Entity;

use App\Repository\ItinerarioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItinerarioRepository::class)
 */
class Itinerario
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
    private $Nombre;

    /**
     * @ORM\ManyToOne(targetEntity=Viaje::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Viaje;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $Precio;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $FechaInicio;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $FechaFin;

    /**
     * @ORM\ManyToOne(targetEntity=Grupo::class)
     */
    private $Grupo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Principal;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $UpdatedAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $CreatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $DeletedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): self
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getViaje(): ?Viaje
    {
        return $this->Viaje;
    }

    public function setViaje(?Viaje $Viaje): self
    {
        $this->Viaje = $Viaje;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->Precio;
    }

    public function setPrecio(?float $Precio): self
    {
        $this->Precio = $Precio;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->FechaInicio;
    }

    public function setFechaInicio(?\DateTimeInterface $FechaInicio): self
    {
        $this->FechaInicio = $FechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->FechaFin;
    }

    public function setFechaFin(?\DateTimeInterface $FechaFin): self
    {
        $this->FechaFin = $FechaFin;

        return $this;
    }

    public function getGrupo(): ?Grupo
    {
        return $this->Grupo;
    }

    public function setGrupo(?Grupo $Grupo): self
    {
        $this->Grupo = $Grupo;

        return $this;
    }

    public function getPrincipal(): ?bool
    {
        return $this->Principal;
    }

    public function setPrincipal(bool $Principal): self
    {
        $this->Principal = $Principal;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $UpdatedAt): self
    {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->DeletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $DeletedAt): self
    {
        $this->DeletedAt = $DeletedAt;

        return $this;
    }
}
