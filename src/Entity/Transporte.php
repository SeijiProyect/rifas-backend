<?php

namespace App\Entity;

use App\Repository\TransporteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransporteRepository::class)
 */
class Transporte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Trayecto::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $trayecto;

    /**
     * @ORM\ManyToOne(targetEntity=TransporteTipo::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $transporte_tipo;

    /**
     * @ORM\ManyToOne(targetEntity=Proveedor::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $proveedor;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_inicio;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_fin;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comentarios;

    /**
     * @ORM\Column(type="float")
     */
    private $orden;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $duracion;

    /**
     * @ORM\ManyToOne(targetEntity=Trayecto::class)
     */
    private $trayecto_padre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hora_inicio;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hora_fin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrayecto(): ?Trayecto
    {
        return $this->trayecto;
    }

    public function setTrayecto(?Trayecto $trayecto): self
    {
        $this->trayecto = $trayecto;

        return $this;
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

    public function getProveedor(): ?Proveedor
    {
        return $this->proveedor;
    }

    public function setProveedor(?Proveedor $proveedor): self
    {
        $this->proveedor = $proveedor;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fecha_inicio;
    }

    public function setFechaInicio(\DateTimeInterface $fecha_inicio): self
    {
        $this->fecha_inicio = $fecha_inicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fecha_fin;
    }

    public function setFechaFin(\DateTimeInterface $fecha_fin): self
    {
        $this->fecha_fin = $fecha_fin;

        return $this;
    }

    public function getComentarios(): ?string
    {
        return $this->comentarios;
    }

    public function setComentarios(?string $comentarios): self
    {
        $this->comentarios = $comentarios;

        return $this;
    }

    public function getOrden(): ?float
    {
        return $this->orden;
    }

    public function setOrden(float $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getDuracion(): ?string
    {
        return $this->duracion;
    }

    public function setDuracion(string $duracion): self
    {
        $this->duracion = $duracion;

        return $this;
    }

    public function getTrayectoPadre(): ?Trayecto
    {
        return $this->trayecto_padre;
    }

    public function setTrayectoPadre(?Trayecto $trayecto_padre): self
    {
        $this->trayecto_padre = $trayecto_padre;

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

    public function getHoraInicio(): ?string
    {
        return $this->hora_inicio;
    }

    public function setHoraInicio(string $hora_inicio): self
    {
        $this->hora_inicio = $hora_inicio;

        return $this;
    }

    public function getHoraFin(): ?string
    {
        return $this->hora_inicio;
    }

    public function setHoraFin(string $hora_fin): self
    {
        $this->hora_fin = $hora_fin;

        return $this;
    }
}
