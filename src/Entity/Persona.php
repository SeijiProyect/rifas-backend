<?php

namespace App\Entity;

use App\Repository\PersonaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonaRepository::class)
 */
class Persona
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
    private $Nombres;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Apellidos;

    /**
     * @ORM\Column(type="date")
     */
    private $FechaNacimiento;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Direccion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Cedula;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Celular;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $Sexo;

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
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="Persona", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idExterno;

    /**
     * @ORM\OneToMany(targetEntity=Documento::class, mappedBy="persona")
     */
    private $documentos;

    /**
     * @ORM\OneToMany(targetEntity=FotoPersona::class, mappedBy="persona")
     */
    private $fotoPersonas;

    /**
     * @ORM\OneToMany(targetEntity=Pasajero::class, mappedBy="Persona", orphanRemoval=true)
     */
    private $pasajeros;

    public function __construct()
    {
        $this->documentos = new ArrayCollection();
        $this->fotoPersonas = new ArrayCollection();
        $this->pasajeros = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getNombres(): ?string
    {
        return $this->Nombres;
    }

    public function setNombres(string $Nombres): self
    {
        $this->Nombres = $Nombres;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->Apellidos;
    }

    public function setApellidos(string $Apellidos): self
    {
        $this->Apellidos = $Apellidos;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->FechaNacimiento;
    }

    public function setFechaNacimiento(\DateTimeInterface $FechaNacimiento): self
    {
        $this->FechaNacimiento = $FechaNacimiento;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->Direccion;
    }

    public function setDireccion(?string $Direccion): self
    {
        $this->Direccion = $Direccion;

        return $this;
    }

    public function getCedula(): ?string
    {
        return $this->Cedula;
    }

    public function setCedula(string $Cedula): self
    {
        $this->Cedula = $Cedula;

        return $this;
    }

    public function getCelular(): ?string
    {
        return $this->Celular;
    }

    public function setCelular(?string $Celular): self
    {
        $this->Celular = $Celular;

        return $this;
    }

    public function getSexo(): ?string
    {
        return $this->Sexo;
    }

    public function setSexo(?string $Sexo): self
    {
        $this->Sexo = $Sexo;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setPersona(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getPersona() !== $this) {
            $user->setPersona($this);
        }

        $this->user = $user;

        return $this;
    }

    public function getIdExterno(): ?int
    {
        return $this->idExterno;
    }

    public function setIdExterno(?int $idExterno): self
    {
        $this->idExterno = $idExterno;

        return $this;
    }

    /**
     * @return Collection|Pasajero[]
     */
    public function getPasajeros(): Collection
    {
        return $this->pasajeros;
    }

    /**
     * @return Collection|Documento[]
     */
    public function getDocumentos(): Collection
    {
        return $this->documentos;
    }

    public function addDocumento(Documento $documento): self
    {
        if (!$this->documentos->contains($documento)) {
            $this->documentos[] = $documento;
            $documento->setPersona($this);
        }

        return $this;
    }

    public function removeDocumento(Documento $documento): self
    {
        if ($this->documentos->removeElement($documento)) {
            // set the owning side to null (unless already changed)
            if ($documento->getPersona() === $this) {
                $documento->setPersona(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FotoPersona[]
     */
    public function getFotoPersonas(): Collection
    {
        return $this->fotoPersonas;
    }

    public function addFotoPersona(FotoPersona $fotoPersona): self
    {
        if (!$this->fotoPersonas->contains($fotoPersona)) {
            $this->fotoPersonas[] = $fotoPersona;
            $fotoPersona->setPersona($this);
        }

        return $this;
    }

    public function removeFotoPersona(FotoPersona $fotoPersona): self
    {
        if ($this->fotoPersonas->removeElement($fotoPersona)) {
            // set the owning side to null (unless already changed)
            if ($fotoPersona->getPersona() === $this) {
                $fotoPersona->setPersona(null);
            }
        }

        return $this;
    }
}
