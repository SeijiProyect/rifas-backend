<?php

namespace App\Entity;

use App\Repository\LoteRifaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LoteRifaRepository::class)
 */
class LoteRifa
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
     * @ORM\Column(type="string", length=255)
     */
    private $Tipo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Estado;

    /**
     * @ORM\Column(type="smallint")
     */
    private $CantidadSorteos;

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

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Moneda;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Anio;

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

    public function getTipo(): ?string
    {
        return $this->Tipo;
    }

    public function setTipo(string $Tipo): self
    {
        $this->Tipo = $Tipo;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->Estado;
    }

    public function setEstado(string $Estado): self
    {
        $this->Estado = $Estado;

        return $this;
    }

    public function getCantidadSorteos(): ?int
    {
        return $this->CantidadSorteos;
    }

    public function setCantidadSorteos(int $CantidadSorteos): self
    {
        $this->CantidadSorteos = $CantidadSorteos;

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

    public function getMoneda(): ?string
    {
        return $this->Moneda;
    }

    public function setMoneda(string $Moneda): self
    {
        $this->Moneda = $Moneda;

        return $this;
    }

    public function getAnio(): ?string
    {
        return $this->Anio;
    }

    public function setAnio(string $Anio): self
    {
        $this->Anio = $Anio;

        return $this;
    }
}
