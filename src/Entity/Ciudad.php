<?php

namespace App\Entity;

use App\Repository\CiudadRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CiudadRepository::class)
 */
class Ciudad
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pais::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pais;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombre_ingles;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $latitud;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $longitud;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image_src;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPais(): ?Pais
    {
        return $this->pais;
    }

    public function setPais(?Pais $pais): self
    {
        $this->pais = $pais;

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

    public function getNombreIngles(): ?string
    {
        return $this->nombre_ingles;
    }

    public function setNombreIngles(?string $nombre_ingles): self
    {
        $this->nombre_ingles = $nombre_ingles;

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

    public function getLatitud(): ?string
    {
        return $this->latitud;
    }

    public function setLatitud(?string $latitud): self
    {
        $this->latitud = $latitud;

        return $this;
    }

    public function getLongitud(): ?string
    {
        return $this->longitud;
    }

    public function setLongitud(?string $longitud): self
    {
        $this->longitud = $longitud;

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
}
