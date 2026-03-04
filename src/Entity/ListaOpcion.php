<?php

namespace App\Entity;

use App\Repository\ListaOpcionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListaOpcionRepository::class)
 */
class ListaOpcion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titulo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cupo_limite;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $precio;

    /**
     * @ORM\ManyToOne(targetEntity=Lista::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $lista;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getCupoLimite(): ?int
    {
        return $this->cupo_limite;
    }

    public function setCupoLimite(?int $cupo_limite): self
    {
        $this->cupo_limite = $cupo_limite;

        return $this;
    }

    public function getPrecio(): ?int
    {
        return $this->precio;
    }

    public function setPrecio(?int $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getLista(): ?Lista
    {
        return $this->lista;
    }

    public function setLista(?Lista $lista): self
    {
        $this->lista = $lista;

        return $this;
    }
}
