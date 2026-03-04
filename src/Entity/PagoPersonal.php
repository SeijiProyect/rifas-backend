<?php

namespace App\Entity;

use App\Repository\PagoPersonalRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PagoPersonalRepository::class)
 */
class PagoPersonal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $Fecha;

    /**
     * @ORM\Column(type="float")
     */
    private $Monto;

    /**
     * @ORM\ManyToOne(targetEntity=Deposito::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Deposito;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMonto(): ?float
    {
        return $this->Monto;
    }

    public function setMonto(float $Monto): self
    {
        $this->Monto = $Monto;

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
}
