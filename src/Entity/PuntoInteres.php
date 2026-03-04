<?php

namespace App\Entity;

use App\Repository\PuntoInteresRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PuntoInteresRepository::class)
 */
class PuntoInteres
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
    private $ciudad;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $maps_me;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $google_maps;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $horarios;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $precio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tipo;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $orden;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image_src;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $web_url;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

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

    public function getMapsMe(): ?string
    {
        return $this->maps_me;
    }

    public function setMapsMe(?string $maps_me): self
    {
        $this->maps_me = $maps_me;

        return $this;
    }

    public function getGoogleMaps(): ?string
    {
        return $this->google_maps;
    }

    public function setGoogleMaps(?string $google_maps): self
    {
        $this->google_maps = $google_maps;

        return $this;
    }

    public function getHorarios(): ?string
    {
        return $this->horarios;
    }

    public function setHorarios(?string $horarios): self
    {
        $this->horarios = $horarios;

        return $this;
    }

    public function getPrecio(): ?string
    {
        return $this->precio;
    }

    public function setPrecio(?string $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getOrden(): ?float
    {
        return $this->orden;
    }

    public function setOrden(?float $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getImageSrc(): ?string
    {
        return $this->image_src;
    }

    public function setImageSrc(?string $image_src): self
    {
        $this->image_src = $image_src;

        return $this;
    }

    public function getWebUrl(): ?string
    {
        return $this->web_url;
    }

    public function setWebUrl(?string $web_url): self
    {
        $this->web_url = $web_url;

        return $this;
    }
}
