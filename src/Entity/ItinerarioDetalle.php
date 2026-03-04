<?php

namespace App\Entity;

use App\Repository\ItinerarioDetalleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItinerarioDetalleRepository::class)
 */
class ItinerarioDetalle
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Itinerario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $itinerario;

    /**
     * @ORM\ManyToOne(targetEntity=Ciudad::class)
     */
    private $ciudad;

    /**
     * @ORM\ManyToOne(targetEntity=Trayecto::class)
     */
    private $trayecto;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_inicio;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_fin;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $orden;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItinerario(): ?Itinerario
    {
        return $this->itinerario;
    }

    public function setItinerario(?Itinerario $itinerario): self
    {
        $this->itinerario = $itinerario;

        return $this;
    }

    public function getCiudad(): ?Ciudad
    {
        return $this->ciudad;
    }

    public function setCiudad(?Ciudad $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getTrayecto(): ?Trayecto
    {
        return $this->trayecto;
    }

    public function setTrayecto(?Trayecto $trayecto): self
    {
        $this->trayecto = $trayecto;

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

    public function getOrden(): ?float
    {
        return $this->orden;
    }

    public function setOrden(?float $orden): self
    {
        $this->orden = $orden;

        return $this;
    }
}
