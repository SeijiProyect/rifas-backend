<?php

namespace App\Entity;

use App\Repository\AlojamientoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AlojamientoRepository::class)
 */
class Alojamiento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=AlojamientoTipo::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $alojamiento_tipo;

    /**
     * @ORM\ManyToOne(targetEntity=Proveedor::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $proveedor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image_src;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlojamientoTipo(): ?AlojamientoTipo
    {
        return $this->alojamiento_tipo;
    }

    public function setAlojamientoTipo(?AlojamientoTipo $alojamiento_tipo): self
    {
        $this->alojamiento_tipo = $alojamiento_tipo;

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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

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
