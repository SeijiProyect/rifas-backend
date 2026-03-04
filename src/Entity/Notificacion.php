<?php

namespace App\Entity;

use App\Repository\NotificacionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificacionRepository::class)
 */
class Notificacion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $titulo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mensaje;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha_programada;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $foto;

    /**
     * @ORM\ManyToOne(targetEntity=Grupo::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $grupo;

    /**
     * @ORM\ManyToOne(targetEntity=Itinerario::class)
     */
    private $itinerario;

    /**
     * @ORM\ManyToOne(targetEntity=Ciudad::class)
     */
    private $ciudad;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha_enviado;

    /**
     * @ORM\ManyToOne(targetEntity=Lista::class)
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

    public function getMensaje(): ?string
    {
        return $this->mensaje;
    }

    public function setMensaje(string $mensaje): self
    {
        $this->mensaje = $mensaje;

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

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): self
    {
        $this->foto = $foto;

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

    public function getItinerario(): ?Itinerario
    {
        return $this->itinerario;
    }

    public function setItinerario(?Itinerario $itinerario): self
    {
        $this->itinerario = $itinerario;

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

    public function getFechaEnviado(): ?\DateTimeInterface
    {
        return $this->fecha_enviado;
    }

    public function setFechaEnviado(?\DateTimeInterface $fecha_enviado): self
    {
        $this->fecha_enviado = $fecha_enviado;

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
