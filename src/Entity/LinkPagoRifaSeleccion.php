<?php

namespace App\Entity;

use App\Repository\LinkPagoRifaSeleccionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinkPagoRifaSeleccionRepository::class)
 */
class LinkPagoRifaSeleccion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=LinkPagoRifa::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $LinkPagoRifa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Tarjeta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $TipoTarjeta;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $Cuotas;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $CreatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $UpdatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $DeletedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLinkPagoRifa(): ?LinkPagoRifa
    {
        return $this->LinkPagoRifa;
    }

    public function setLinkPagoRifa(?LinkPagoRifa $LinkPagoRifa): self
    {
        $this->LinkPagoRifa = $LinkPagoRifa;

        return $this;
    }

    public function getTarjeta(): ?string
    {
        return $this->Tarjeta;
    }

    public function setTarjeta(?string $Tarjeta): self
    {
        $this->Tarjeta = $Tarjeta;

        return $this;
    }

    public function getTipoTarjeta(): ?string
    {
        return $this->TipoTarjeta;
    }

    public function setTipoTarjeta(?string $TipoTarjeta): self
    {
        $this->TipoTarjeta = $TipoTarjeta;

        return $this;
    }

    public function getCuotas(): ?int
    {
        return $this->Cuotas;
    }

    public function setCuotas(?int $Cuotas): self
    {
        $this->Cuotas = $Cuotas;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $UpdatedAt): self
    {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->DeletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $DeletedAt): self
    {
        $this->DeletedAt = $DeletedAt;

        return $this;
    }
}
