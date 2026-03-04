<?php

namespace App\Entity;

use App\Repository\CompradorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompradorRepository::class)
 */
class Comprador
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Celular;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Departamento;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
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

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): self
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    public function getCelular(): ?string
    {
        return $this->Celular;
    }

    public function setCelular(string $Celular): self
    {
        $this->Celular = $Celular;

        return $this;
    }

    public function getDepartamento(): ?string
    {
        return $this->Departamento;
    }

    public function setDepartamento(?string $Departamento): self
    {
        $this->Departamento = $Departamento;

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
