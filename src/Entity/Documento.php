<?php

namespace App\Entity;

use App\Repository\DocumentoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentoRepository::class)
 */

class Documento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $serie;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fecha_expedicion;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fecha_vencimiento;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $imagen_url;

    /**
     * @ORM\ManyToOne(targetEntity=TipoDocumento::class, inversedBy="documentos")
     */
    private $tipoDocumento;

    /**
     * @ORM\ManyToOne(targetEntity=Pais::class, inversedBy="documentos")
     */
    private $pais;

    /**
     * @ORM\ManyToOne(targetEntity=Persona::class, inversedBy="documentos")
     */
    private $persona;

    /**
     * @ORM\OneToMany(targetEntity=Archivo::class, mappedBy="documento")
     */
    private $archivos;

    /**
     * @ORM\OneToMany(targetEntity=PasajeroDocumento::class, mappedBy="documento")
     */
    private $pasajeroDocumentos;

    public function __construct()
    {
        $this->archivos = new ArrayCollection();
        $this->pasajeroDocumentos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getSerie(): ?string
    {
        return $this->serie;
    }

    public function setSerie(?string $serie): self
    {
        $this->serie = $serie;

        return $this;
    }

    public function getFechaExpedicion(): ?\DateTimeInterface
    {
        return $this->fecha_expedicion;
    }

    public function setFechaExpedicion(?\DateTimeInterface $fecha_expedicion): self
    {
        $this->fecha_expedicion = $fecha_expedicion;

        return $this;
    }

    public function getFechaVencimiento(): ?\DateTimeInterface
    {
        return $this->fecha_vencimiento;
    }

    public function setFechaVencimiento(?\DateTimeInterface $fecha_vencimiento): self
    {
        $this->fecha_vencimiento = $fecha_vencimiento;

        return $this;
    }

    public function getImagenUrl(): ?string
    {
        return $this->imagen_url;
    }

    public function setImagenUrl(?string $imagen_url): self
    {
        $this->imagen_url = $imagen_url;

        return $this;
    }

    public function getTipoDocumento(): ?TipoDocumento
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(?TipoDocumento $tipoDocumento): self
    {
        $this->tipoDocumento = $tipoDocumento;

        return $this;
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

    public function getPersona(): ?Persona
    {
        return $this->persona;
    }

    public function setPersona(?Persona $persona): self
    {
        $this->persona = $persona;

        return $this;
    }

    /**
     * @return Collection|Archivo[]
     */
    public function getArchivos(): Collection
    {
        return $this->archivos;
    }

    public function addArchivo(Archivo $archivo): self
    {
        if (!$this->archivos->contains($archivo)) {
            $this->archivos[] = $archivo;
            $archivo->setDocumento($this);
        }

        return $this;
    }

    public function removeArchivo(Archivo $archivo): self
    {
        if ($this->archivos->removeElement($archivo)) {
            // set the owning side to null (unless already changed)
            if ($archivo->getDocumento() === $this) {
                $archivo->setDocumento(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PasajeroDocumento[]
     */
    public function getPasajeroDocumentos(): Collection
    {
        return $this->pasajeroDocumentos;
    }

    public function addPasajeroDocumento(PasajeroDocumento $pasajeroDocumento): self
    {
        if (!$this->pasajeroDocumentos->contains($pasajeroDocumento)) {
            $this->pasajeroDocumentos[] = $pasajeroDocumento;
            $pasajeroDocumento->setDocumento($this);
        }

        return $this;
    }

    public function removePasajeroDocumento(PasajeroDocumento $pasajeroDocumento): self
    {
        if ($this->pasajeroDocumentos->removeElement($pasajeroDocumento)) {
            // set the owning side to null (unless already changed)
            if ($pasajeroDocumento->getDocumento() === $this) {
                $pasajeroDocumento->setDocumento(null);
            }
        }

        return $this;
    }

}
