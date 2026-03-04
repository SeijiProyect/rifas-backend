<?php

namespace App\Entity;

use App\Repository\CostoExtraRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CostoExtraRepository::class)
 */
class CostoExtra
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
    private $Descripcion;

    /**
     * @ORM\Column(type="float")
     */
    private $Monto;

    /**
     * @ORM\Column(type="datetime")
     */
    private $Fecha;

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

    public function getDescripcion(): ?string
    {
        return $this->Descripcion;
    }

    public function setDescripcion(string $Descripcion): self
    {
        $this->Descripcion = $Descripcion;

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

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->Fecha;
    }

    public function setFecha(\DateTimeInterface $Fecha): self
    {
        $this->Fecha = $Fecha;

        return $this;
    }
}
