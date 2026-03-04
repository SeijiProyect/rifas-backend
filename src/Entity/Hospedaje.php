<?php

namespace App\Entity;

use App\Repository\HospedajeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HospedajeRepository::class)
 */
class Hospedaje
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Alojamiento::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $alojamiento;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_desde;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_hasta;

    /**
     * @ORM\Column(type="text")
     */
    private $habitaciones;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comentarios;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getFechaDesde(): ?\DateTimeInterface
    {
        return $this->fecha_desde;
    }

    public function setFechaDesde(\DateTimeInterface $fecha_desde): self
    {
        $this->fecha_desde = $fecha_desde;

        return $this;
    }

    public function getFechaHasta(): ?\DateTimeInterface
    {
        return $this->fecha_hasta;
    }

    public function setFechaHasta(\DateTimeInterface $fecha_hasta): self
    {
        $this->fecha_hasta = $fecha_hasta;

        return $this;
    }

    public function getHabitaciones(): ?string
    {
        return $this->habitaciones;
    }

    public function setHabitaciones(string $habitaciones): self
    {
        $this->habitaciones = $habitaciones;

        return $this;
    }

    public function getComentarios(): ?string
    {
        return $this->comentarios;
    }

    public function setComentarios(?string $comentarios): self
    {
        $this->comentarios = $comentarios;

        return $this;
    }
}
