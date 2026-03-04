<?php

namespace App\Entity;

use App\Repository\DepositoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DepositoRepository::class)
 */
class Deposito
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $Monto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Tipo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $Fecha;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $FechaProcesado;

    /**
     * @ORM\ManyToOne(targetEntity=Pasajero::class, inversedBy="depositos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Pasajero;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Csv;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Comentario;

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

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $geopay;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonto(): ?float
    {
        return $this->Monto;
    }

    public function setMonto(float $Monto): self
    {
        $this->Monto = $Monto;

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

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->Fecha;
    }

    public function setFecha(\DateTimeInterface $Fecha): self
    {
        $this->Fecha = $Fecha;

        return $this;
    }

    public function getFechaProcesado(): ?\DateTimeInterface
    {
        return $this->FechaProcesado;
    }

    public function setFechaProcesado(?\DateTimeInterface $FechaProcesado): self
    {
        $this->FechaProcesado = $FechaProcesado;

        return $this;
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

    public function getCsv(): ?bool
    {
        return $this->Csv;
    }

    public function setCsv(bool $Csv): self
    {
        $this->Csv = $Csv;

        return $this;
    }

    public function getComentario(): ?string
    {
        return $this->Comentario;
    }

    public function setComentario(?string $Comentario): self
    {
        $this->Comentario = $Comentario;

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

    public function getGeopay(): ?bool
    {
        return $this->geopay;
    }

    public function setGeopay(?bool $geopay): self
    {
        $this->geopay = $geopay;

        return $this;
    }
}
