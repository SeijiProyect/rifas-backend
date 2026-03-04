<?php

namespace App\Entity;

use App\Repository\TrayectoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrayectoRepository::class)
 */
class Trayecto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Ciudad::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $ciudad_inicio;

    /**
     * @ORM\ManyToOne(targetEntity=Ciudad::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $ciudad_fin;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $generico;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCiudadInicio(): ?Ciudad
    {
        return $this->ciudad_inicio;
    }

    public function setCiudadInicio(?Ciudad $ciudad_inicio): self
    {
        $this->ciudad_inicio = $ciudad_inicio;

        return $this;
    }

    public function getCiudadFin(): ?Ciudad
    {
        return $this->ciudad_fin;
    }

    public function setCiudadFin(?Ciudad $ciudad_fin): self
    {
        $this->ciudad_fin = $ciudad_fin;

        return $this;
    }

    public function isGenerico(): ?bool
    {
        return $this->generico;
    }

    public function setGenerico(?bool $generico): self
    {
        $this->generico = $generico;

        return $this;
    }
}
