<?php

namespace App\Entity;

use App\Repository\CamposTipotransporteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CamposTipotransporteRepository::class)
 */
class CamposTipotransporte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TransporteTipo::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $transporte_tipo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $obligatorio;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $aeropuerto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransporteTipo(): ?TransporteTipo
    {
        return $this->transporte_tipo;
    }

    public function setTransporteTipo(?TransporteTipo $transporte_tipo): self
    {
        $this->transporte_tipo = $transporte_tipo;

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

    public function isObligatorio(): ?bool
    {
        return $this->obligatorio;
    }

    public function setObligatorio(?bool $obligatorio): self
    {
        $this->obligatorio = $obligatorio;

        return $this;
    }

    public function isAeropuerto(): ?bool
    {
        return $this->aeropuerto;
    }

    public function setAeropuerto(?bool $aeropuerto): self
    {
        $this->aeropuerto = $aeropuerto;

        return $this;
    }
}
