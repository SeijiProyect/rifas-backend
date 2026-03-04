<?php

namespace App\Entity;

use App\Repository\DatosTipoTransporteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DatosTipoTransporteRepository::class)
 */
class DatosTipoTransporte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CamposTipotransporte::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $campos_tipo_transporte;

    /**
     * @ORM\ManyToOne(targetEntity=Transporte::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $transporte;

    /**
     * @ORM\ManyToOne(targetEntity=Aereopuerto::class)
     */
    private $aereopuerto;

    /**
     * @ORM\Column(type="text")
     */
    private $valor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCamposTipoTransporte(): ?CamposTipotransporte
    {
        return $this->campos_tipo_transporte;
    }

    public function setCamposTipoTransporte(?CamposTipotransporte $campos_tipo_transporte): self
    {
        $this->campos_tipo_transporte = $campos_tipo_transporte;

        return $this;
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

    public function getAereopuerto(): ?Aereopuerto
    {
        return $this->aereopuerto;
    }

    public function setAereopuerto(?Aereopuerto $aereopuerto): self
    {
        $this->aereopuerto = $aereopuerto;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;

        return $this;
    }
}
