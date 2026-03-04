<?php

namespace App\Entity;

use App\Repository\TarjetaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TarjetaRepository::class)
 */
class Tarjeta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Deposito::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $Deposito;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Issuer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Moneda;

    /**
     * @ORM\Column(type="smallint")
     */
    private $Cuotas;

    /**
     * @ORM\Column(type="datetime")
     */
    private $FechaTransaccion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CodigoAutorizacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $NumeroTarjeta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Acquirer;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $NombreTarjeta;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $FechaVencimiento;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeposito(): ?Deposito
    {
        return $this->Deposito;
    }

    public function setDeposito(Deposito $Deposito): self
    {
        $this->Deposito = $Deposito;

        return $this;
    }

    public function getIssuer(): ?string
    {
        return $this->Issuer;
    }

    public function setIssuer(string $Issuer): self
    {
        $this->Issuer = $Issuer;

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

    public function getCuotas(): ?int
    {
        return $this->Cuotas;
    }

    public function setCuotas(int $Cuotas): self
    {
        $this->Cuotas = $Cuotas;

        return $this;
    }

    public function getFechaTransaccion(): ?\DateTimeInterface
    {
        return $this->FechaTransaccion;
    }

    public function setFechaTransaccion(\DateTimeInterface $FechaTransaccion): self
    {
        $this->FechaTransaccion = $FechaTransaccion;

        return $this;
    }

    public function getCodigoAutorizacion(): ?string
    {
        return $this->CodigoAutorizacion;
    }

    public function setCodigoAutorizacion(string $CodigoAutorizacion): self
    {
        $this->CodigoAutorizacion = $CodigoAutorizacion;

        return $this;
    }

    public function getNumeroTarjeta(): ?string
    {
        return $this->NumeroTarjeta;
    }

    public function setNumeroTarjeta(?string $NumeroTarjeta): self
    {
        $this->NumeroTarjeta = $NumeroTarjeta;

        return $this;
    }

    public function getAcquirer(): ?string
    {
        return $this->Acquirer;
    }

    public function setAcquirer(?string $Acquirer): self
    {
        $this->Acquirer = $Acquirer;

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

    public function getNombreTarjeta(): ?string
    {
        return $this->NombreTarjeta;
    }

    public function setNombreTarjeta(?string $NombreTarjeta): self
    {
        $this->NombreTarjeta = $NombreTarjeta;

        return $this;
    }

    public function getFechaVencimiento(): ?string
    {
        return $this->FechaVencimiento;
    }

    public function setFechaVencimiento(?string $FechaVencimiento): self
    {
        $this->FechaVencimiento = $FechaVencimiento;

        return $this;
    }
}
