<?php

namespace App\Entity;

use App\Repository\ViajeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ViajeRepository::class)
 */
class Viaje
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ViajeMadre::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $ViajeMadre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Descripcion;

    /**
     * @ORM\Column(type="smallint")
     */
    private $Anio;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Activo;

    /**
     * @ORM\Column(type="date")
     */
    private $FechaInicio;

    /**
     * @ORM\Column(type="date")
     */
    private $FechaFin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Titulo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Subtitulo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Destacado;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Token;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $CreatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $UpdatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $DeletedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getViajeMadre(): ?ViajeMadre
    {
        return $this->ViajeMadre;
    }

    public function setViajeMadre(?ViajeMadre $ViajeMadre): self
    {
        $this->ViajeMadre = $ViajeMadre;

        return $this;
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

    public function getDescripcion(): ?string
    {
        return $this->Descripcion;
    }

    public function setDescripcion(?string $Descripcion): self
    {
        $this->Descripcion = $Descripcion;

        return $this;
    }

    public function getAnio(): ?int
    {
        return $this->Anio;
    }

    public function setAnio(int $Anio): self
    {
        $this->Anio = $Anio;

        return $this;
    }

    public function getActivo(): ?bool
    {
        return $this->Activo;
    }

    public function setActivo(bool $Activo): self
    {
        $this->Activo = $Activo;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->FechaInicio;
    }

    public function setFechaInicio(\DateTimeInterface $FechaInicio): self
    {
        $this->FechaInicio = $FechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->FechaFin;
    }

    public function setFechaFin(\DateTimeInterface $FechaFin): self
    {
        $this->FechaFin = $FechaFin;

        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->Titulo;
    }

    public function setTitulo(?string $Titulo): self
    {
        $this->Titulo = $Titulo;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->Token;
    }

    public function setToken(?string $Token): self
    {
        $this->Token = $Token;

        return $this;
    }

    public function getSubtitulo(): ?string
    {
        return $this->Subtitulo;
    }

    public function setSubtitulo(?string $Subtitulo): self
    {
        $this->Subtitulo = $Subtitulo;

        return $this;
    }

    public function getDestacado(): ?bool
    {
        return $this->Destacado;
    }

    public function setDestacado(bool $Destacado): self
    {
        $this->Destacado = $Destacado;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $UpdatedAt): self
    {
        $this->UpdatedAt = $UpdatedAt;

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
