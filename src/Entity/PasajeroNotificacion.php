<?php

namespace App\Entity;

use App\Repository\PasajeroNotificacionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PasajeroNotificacionRepository::class)
 */
class PasajeroNotificacion
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
     * @ORM\ManyToOne(targetEntity=Notificacion::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $notificacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha_visto;

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

    public function getNotificacion(): ?Notificacion
    {
        return $this->notificacion;
    }

    public function setNotificacion(?Notificacion $notificacion): self
    {
        $this->notificacion = $notificacion;

        return $this;
    }

    public function getFechaVisto(): ?\DateTimeInterface
    {
        return $this->fecha_visto;
    }

    public function setFechaVisto(\DateTimeInterface $fecha_visto): self
    {
        $this->fecha_visto = $fecha_visto;

        return $this;
    }
}
