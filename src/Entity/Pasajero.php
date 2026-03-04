<?php

namespace App\Entity;

use App\Repository\PasajeroRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PasajeroRepository::class)
 */
class Pasajero
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity=Persona::class, inversedBy="pasajeros")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Persona;

    /**
     * @ORM\ManyToOne(targetEntity=Itinerario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Itinerario;

    /**
     * @ORM\ManyToOne(targetEntity=Universidad::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Universidad;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Estado;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Comentarios;

    /**
     * @ORM\OneToOne(targetEntity=Pasajero::class, cascade={"persist", "remove"})
     */
    private $Acompanante;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Pasaporte;


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
     * @ORM\OneToMany(targetEntity=EtiquetaPasajero::class, mappedBy="pasajero")
     */
    private $etiquetaPasajeros;

    /**
     * @ORM\OneToMany(targetEntity=PasajeroDocumento::class, mappedBy="pasajero")
     */
    private $pasajeroDocumentos;

    public function __construct()
    {
        $this->etiquetaPasajeros = new ArrayCollection();
        $this->pasajeroDocumentos = new ArrayCollection();
    }

    /**
     * @ORM\OneToMany(targetEntity=Deposito::class, mappedBy="Pasajero")
     */
    // private $depositos;

    // public function __construct()
    // {
    //     $this->depositos = new ArrayCollection();
    // }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $Id): self
    {
        $this->id = $Id;

        return $this;
    }

    public function getPersona(): ?Persona
    {
        return $this->Persona;
    }

    public function setPersona(?Persona $Persona): self
    {
        $this->Persona = $Persona;

        return $this;
    }

    public function getItinerario(): ?Itinerario
    {
        return $this->Itinerario;
    }

    public function setItinerario(?Itinerario $Itinerario): self
    {
        $this->Itinerario = $Itinerario;

        return $this;
    }

    public function getUniversidad(): ?Universidad
    {
        return $this->Universidad;
    }

    public function setUniversidad(?Universidad $Universidad): self
    {
        $this->Universidad = $Universidad;

        return $this;
    }

    public function getPasaporte(): ?string
    {
        return $this->Pasaporte;
    }

    public function setPasaporte(?string $Pasaporte): self
    {
        $this->Pasaporte = $Pasaporte;

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

    public function getComentarios(): ?string
    {
        return $this->Comentarios;
    }

    public function setComentarios(?string $Comentarios): self
    {
        $this->Comentarios = $Comentarios;

        return $this;
    }

    public function getAcompanante(): ?self
    {
        return $this->Acompanante;
    }

    public function setAcompanante(?self $Acompanante): self
    {
        $this->Acompanante = $Acompanante;

        return $this;
    }

    // /**
    //  * @return Collection|Deposito[]
    //  */
    // public function getDepositos(): Collection
    // {
    //     return $this->depositos;
    // }

    // public function addDeposito(Deposito $deposito): self
    // {
    //     if (!$this->depositos->contains($deposito)) {
    //         $this->depositos[] = $deposito;
    //         $deposito->setPasajero($this);
    //     }

    //     return $this;
    // }

  /*  public function removeDeposito(Deposito $deposito): self
    {
        if ($this->depositos->removeElement($deposito)) {
            // set the owning side to null (unless already changed)
            if ($deposito->getPasajero() === $this) {
                $deposito->setPasajero(null);
            }
        }

        return $this;
    }*/

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

    /**
     * @return Collection|EtiquetaPasajero[]
     */
    public function getEtiquetaPasajeros(): Collection
    {
        return $this->etiquetaPasajeros;
    }

    public function addEtiquetaPasajero(EtiquetaPasajero $etiquetaPasajero): self
    {
        if (!$this->etiquetaPasajeros->contains($etiquetaPasajero)) {
            $this->etiquetaPasajeros[] = $etiquetaPasajero;
            $etiquetaPasajero->setPasajero($this);
        }

        return $this;
    }

    public function removeEtiquetaPasajero(EtiquetaPasajero $etiquetaPasajero): self
    {
        if ($this->etiquetaPasajeros->removeElement($etiquetaPasajero)) {
            // set the owning side to null (unless already changed)
            if ($etiquetaPasajero->getPasajero() === $this) {
                $etiquetaPasajero->setPasajero(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PasajeroDocumento[]
     */
    public function getPasajeroDocumentos(): Collection
    {
        return $this->pasajeroDocumentos;
    }

    public function addPasajeroDocumento(PasajeroDocumento $pasajeroDocumento): self
    {
        if (!$this->pasajeroDocumentos->contains($pasajeroDocumento)) {
            $this->pasajeroDocumentos[] = $pasajeroDocumento;
            $pasajeroDocumento->setPasajero($this);
        }

        return $this;
    }

    public function removePasajeroDocumento(PasajeroDocumento $pasajeroDocumento): self
    {
        if ($this->pasajeroDocumentos->removeElement($pasajeroDocumento)) {
            // set the owning side to null (unless already changed)
            if ($pasajeroDocumento->getPasajero() === $this) {
                $pasajeroDocumento->setPasajero(null);
            }
        }

        return $this;
    }

}
