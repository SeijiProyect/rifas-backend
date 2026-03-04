<?php

namespace App\Entity;

use App\Repository\PasajeroDocumentoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PasajeroDocumentoRepository::class)
 */
class PasajeroDocumento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha_creado;

    /**
     * @ORM\ManyToOne(targetEntity=Pasajero::class, inversedBy="pasajeroDocumentos")
     */
    private $pasajero;

    /**
     * @ORM\ManyToOne(targetEntity=Documento::class, inversedBy="pasajeroDocumentos")
     */
    private $documento;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaCreado(): ?\DateTimeInterface
    {
        return $this->fecha_creado;
    }

    public function setFechaCreado(\DateTimeInterface $fecha_creado): self
    {
        $this->fecha_creado = $fecha_creado;

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

    public function getDocumento(): ?Documento
    {
        return $this->documento;
    }

    public function setDocumento(?Documento $documento): self
    {
        $this->documento = $documento;

        return $this;
    }
}
