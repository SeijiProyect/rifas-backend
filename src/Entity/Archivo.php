<?php

namespace App\Entity;

use App\Repository\ArchivoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArchivoRepository::class)
 */
class Archivo
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
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_creado;

    /**
     * @ORM\ManyToOne(targetEntity=Documento::class, inversedBy="archivos")
     */
    private $documento;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFechaCreado(): ?\DateTimeInterface
    {
        return $this->fecha_creado;
    }

    public function setFechaCreado(\DateTimeInterface $fecha_creado): self
    {
        $this->fecha_creado = $fecha_creado;

        return $this;
    }

    public function getDocumento(): ?Documento
    {
        return $this->documento;
    }

    public function setDocumento(?Documento $documento): self
    {
        $this->documento = $documento;

        return $this;
    }
}
