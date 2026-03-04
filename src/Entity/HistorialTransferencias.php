<?php

namespace App\Entity;

use App\Repository\HistorialTransferenciasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HistorialTransferenciasRepository::class)
 */
class HistorialTransferencias
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Talon::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Talon;

    /**
     * @ORM\Column(type="datetime")
     */
    private $Fecha;

    /**
     * @ORM\ManyToOne(targetEntity=Pasajero::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Pasajero;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Accion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTalon(): ?Talon
    {
        return $this->Talon;
    }

    public function setTalon(?Talon $Talon): self
    {
        $this->Talon = $Talon;

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

    public function getPasajero(): ?Pasajero
    {
        return $this->Pasajero;
    }

    public function setPasajero(?Pasajero $Pasajero): self
    {
        $this->Pasajero = $Pasajero;

        return $this;
    }

    public function getAccion(): ?string
    {
        return $this->Accion;
    }

    public function setAccion(string $Accion): self
    {
        $this->Accion = $Accion;

        return $this;
    }
}
