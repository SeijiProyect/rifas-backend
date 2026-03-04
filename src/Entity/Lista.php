<?php

namespace App\Entity;

use App\Repository\ListaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ListaRepository::class)
 */
class Lista
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
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity=Grupo::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $grupo;

    /**
     * @ORM\ManyToOne(targetEntity=Ciudad::class)
     */
    private $ciudad;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limite_opciones;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $disponible_hasta;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha_programada;

    /**
     * @ORM\ManyToOne(targetEntity=ListaEstado::class)
     */
    private $lista_estado;

    /**
     * @ORM\ManyToOne(targetEntity=Itinerario::class)
     */
    private $itinerario;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imagen;

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

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

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

    public function getCiudad(): ?Ciudad
    {
        return $this->ciudad;
    }

    public function setCiudad(?Ciudad $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getLimiteOpciones(): ?int
    {
        return $this->limite_opciones;
    }

    public function setLimiteOpciones(?int $limite_opciones): self
    {
        $this->limite_opciones = $limite_opciones;

        return $this;
    }

    public function getDisponibleHasta(): ?\DateTimeInterface
    {
        return $this->disponible_hasta;
    }

    public function setDisponibleHasta(?\DateTimeInterface $disponible_hasta): self
    {
        $this->disponible_hasta = $disponible_hasta;

        return $this;
    }

    public function getFechaProgramada(): ?\DateTimeInterface
    {
        return $this->fecha_programada;
    }

    public function setFechaProgramada(?\DateTimeInterface $fecha_programada): self
    {
        $this->fecha_programada = $fecha_programada;

        return $this;
    }

    public function getListaEstado(): ?ListaEstado
    {
        return $this->lista_estado;
    }

    public function setListaEstado(?ListaEstado $lista_estado): self
    {
        $this->lista_estado = $lista_estado;

        return $this;
    }

    public function getItinerario(): ?Itinerario
    {
        return $this->itinerario;
    }

    public function setItinerario(?Itinerario $itinerario): self
    {
        $this->itinerario = $itinerario;

        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(?string $imagen): self
    {
        $this->imagen = $imagen;

        return $this;
    }
}
