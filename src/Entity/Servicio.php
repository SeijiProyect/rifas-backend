<?php

namespace App\Entity;

use App\Repository\ServicioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ServicioRepository::class)
 */
class Servicio
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Transporte::class)
     */
    private $transporte;

    /**
     * @ORM\ManyToOne(targetEntity=Hospedaje::class)
     */
    private $hospedaje;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cantidad;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nro_booking;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $precio_por_persona;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comentarios;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity=Grupo::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $grupo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransporte(): ?Transporte
    {
        return $this->transporte;
    }

    public function setTransporte(?Transporte $transporte): self
    {
        $this->transporte = $transporte;

        return $this;
    }

    public function getHospedaje(): ?Hospedaje
    {
        return $this->hospedaje;
    }

    public function setHospedaje(?Hospedaje $hospedaje): self
    {
        $this->hospedaje = $hospedaje;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getNroBooking(): ?string
    {
        return $this->nro_booking;
    }

    public function setNroBooking(string $nro_booking): self
    {
        $this->nro_booking = $nro_booking;

        return $this;
    }

    public function getPrecioPorPersona(): ?float
    {
        return $this->precio_por_persona;
    }

    public function setPrecioPorPersona(float $precio_por_persona): self
    {
        $this->precio_por_persona = $precio_por_persona;

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

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getGrupo(): ?Grupo
    {
        return $this->grupo;
    }

    public function setGrupo(?Grupo $grupo): self
    {
        $this->grupo = $grupo;

        return $this;
    }
}
