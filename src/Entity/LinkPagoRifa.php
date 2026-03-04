<?php

namespace App\Entity;

use App\Repository\LinkPagoRifaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinkPagoRifaRepository::class)
 */
class LinkPagoRifa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pasajero::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Pasajero;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CompradorNombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CompradorApellido;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CompradorEmail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CompradorCelular;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $CompradorDepartamento;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Estado;

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
    private $EncryptedLink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $GeocomToken;

    /**
     * @ORM\OneToOne(targetEntity=Deposito::class, cascade={"persist", "remove"})
     */
    private $Deposito;

    /**
     * @ORM\Column(type="boolean")
     */
    private $AsumirRecargo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPasajero(): ?Pasajero
    {
        return $this->Pasajero;
    }

    public function setPasajero(?Pasajero $Pasajero): self
    {
        $this->Pasajero = $Pasajero;

        return $this;
    }

    public function getCompradorNombre(): ?string
    {
        return $this->CompradorNombre;
    }

    public function setCompradorNombre(string $CompradorNombre): self
    {
        $this->CompradorNombre = $CompradorNombre;

        return $this;
    }

    public function getCompradorApellido(): ?string
    {
        return $this->CompradorApellido;
    }

    public function setCompradorApellido(string $CompradorApellido): self
    {
        $this->CompradorApellido = $CompradorApellido;

        return $this;
    }

    public function getCompradorEmail(): ?string
    {
        return $this->CompradorEmail;
    }

    public function setCompradorEmail(string $CompradorEmail): self
    {
        $this->CompradorEmail = $CompradorEmail;

        return $this;
    }

    public function getCompradorCelular(): ?string
    {
        return $this->CompradorCelular;
    }

    public function setCompradorCelular(string $CompradorCelular): self
    {
        $this->CompradorCelular = $CompradorCelular;

        return $this;
    }

    public function getCompradorDepartamento(): ?string
    {
        return $this->CompradorDepartamento;
    }

    public function setCompradorDepartamento(?string $CompradorDepartamento): self
    {
        $this->CompradorDepartamento = $CompradorDepartamento;

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

    public function getDeletedAt(): ?string
    {
        return $this->DeletedAt;
    }

    public function setDeletedAt(string $DeletedAt): self
    {
        $this->DeletedAt = $DeletedAt;

        return $this;
    }

    public function getEncryptedLink(): ?string
    {
        return $this->EncryptedLink;
    }

    public function setEncryptedLink(string $EncryptedLink): self
    {
        $this->EncryptedLink = $EncryptedLink;

        return $this;
    }

    public function getGeocomToken(): ?string
    {
        return $this->GeocomToken;
    }

    public function setGeocomToken(?string $GeocomToken): self
    {
        $this->GeocomToken = $GeocomToken;

        return $this;
    }

    public function getDeposito(): ?Deposito
    {
        return $this->Deposito;
    }

    public function setDeposito(?Deposito $Deposito): self
    {
        $this->Deposito = $Deposito;

        return $this;
    }

    public function getAsumirRecargo(): ?bool
    {
        return $this->AsumirRecargo;
    }

    public function setAsumirRecargo(bool $AsumirRecargo): self
    {
        $this->AsumirRecargo = $AsumirRecargo;

        return $this;
    }
}
