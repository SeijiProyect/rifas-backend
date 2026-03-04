<?php

namespace App\Entity;

use App\Repository\PasajeroAlojamientoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PasajeroAlojamientoRepository::class)
 */
class PasajeroAlojamiento
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
    private $pasajero;

    /**
     * @ORM\ManyToOne(targetEntity=Alojamiento::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $alojamiento;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_inicio;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_fin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPasajero(): ?Pasajero
    {
        return $this->pasajero;
    }

    public function setPasajero(?Pasajero $pasajero): self
    {
        $this->pasajero = $pasajero;

        return $this;
    }

    public function getAlojamiento(): ?Alojamiento
    {
        return $this->alojamiento;
    }

    public function setAlojamiento(?Alojamiento $alojamiento): self
    {
        $this->alojamiento = $alojamiento;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fecha_inicio;
    }

    public function setFechaInicio(\DateTimeInterface $fecha_inicio): self
    {
        $this->fecha_inicio = $fecha_inicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fecha_fin;
    }

    public function setFechaFin(\DateTimeInterface $fecha_fin): self
    {
        $this->fecha_fin = $fecha_fin;

        return $this;
    }
}
