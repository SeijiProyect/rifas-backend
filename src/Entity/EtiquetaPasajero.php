<?php

namespace App\Entity;

use App\Repository\EtiquetaPasajeroRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EtiquetaPasajeroRepository::class)
 */
class EtiquetaPasajero
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Etiqueta::class, inversedBy="etiquetaPasajeros")
     */
    private $etiqueta;

    /**
     * @ORM\ManyToOne(targetEntity=Pasajero::class, inversedBy="etiquetaPasajeros")
     */
    private $pasajero;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comentario;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtiqueta(): ?Etiqueta
    {
        return $this->etiqueta;
    }

    public function setEtiqueta(?Etiqueta $etiqueta): self
    {
        $this->etiqueta = $etiqueta;

        return $this;
    }

    public function getPasajero(): ?Pasajero
    {
        return $this->pasajero;
    }

    public function setPasajero(?Pasajero $pasajero): self
    {
        $this->pasajero = $pasajero;

        return $this;
    }

    public function getComentario(): ?string
    {
        return $this->comentario;
    }

    public function setComentario(?string $comentario): self
    {
        $this->comentario = $comentario;

        return $this;
    }
}
