<?php

namespace App\Entity;

use App\Repository\PasajeroListaOpcionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PasajeroListaOpcionRepository::class)
 */
class PasajeroListaOpcion
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
     * @ORM\ManyToOne(targetEntity=ListaOpcion::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $lista_opcion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha_anotado;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha_pago;

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

    public function getListaOpcion(): ?ListaOpcion
    {
        return $this->lista_opcion;
    }

    public function setListaOpcion(?ListaOpcion $lista_opcion): self
    {
        $this->lista_opcion = $lista_opcion;

        return $this;
    }

    public function getFechaAnotado(): ?\DateTimeInterface
    {
        return $this->fecha_anotado;
    }

    public function setFechaAnotado(?\DateTimeInterface $fecha_anotado): self
    {
        $this->fecha_anotado = $fecha_anotado;

        return $this;
    }

    public function getFechaPago(): ?\DateTimeInterface
    {
        return $this->fecha_pago;
    }

    public function setFechaPago(?\DateTimeInterface $fecha_pago): self
    {
        $this->fecha_pago = $fecha_pago;

        return $this;
    }
}
