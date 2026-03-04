<?php

namespace App\Entity;

use App\Repository\PasajeroServicioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PasajeroServicioRepository::class)
 */
class PasajeroServicio
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Servicio::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $servicio;

    /**
     * @ORM\ManyToOne(targetEntity=Pasajero::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pasajero;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $booking;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $estado;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comentario;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServicio(): ?Servicio
    {
        return $this->servicio;
    }

    public function setServicio(?Servicio $servicio): self
    {
        $this->servicio = $servicio;

        return $this;
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

    public function getBooking(): ?string
    {
        return $this->booking;
    }

    public function setBooking(string $booking): self
    {
        $this->booking = $booking;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getComentario(): ?string
    {
        return $this->comentario;
    }

    public function setComentario(?string $comentario): self
    {
        $this->comentario = $comentario;

        return $this;
    }
}
