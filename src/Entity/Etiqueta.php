<?php

namespace App\Entity;

use App\Repository\EtiquetaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EtiquetaRepository::class)
 */
class Etiqueta
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
     * @ORM\OneToMany(targetEntity=EtiquetaPasajero::class, mappedBy="etiqueta")
     */
    private $etiquetaPasajeros;

    public function __construct()
    {
        $this->etiquetaPasajeros = new ArrayCollection();
    }

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

    /**
     * @return Collection|EtiquetaPasajero[]
     */
    public function getEtiquetaPasajeros(): Collection
    {
        return $this->etiquetaPasajeros;
    }

    public function addEtiquetaPasajero(EtiquetaPasajero $etiquetaPasajero): self
    {
        if (!$this->etiquetaPasajeros->contains($etiquetaPasajero)) {
            $this->etiquetaPasajeros[] = $etiquetaPasajero;
            $etiquetaPasajero->setEtiqueta($this);
        }

        return $this;
    }

    public function removeEtiquetaPasajero(EtiquetaPasajero $etiquetaPasajero): self
    {
        if ($this->etiquetaPasajeros->removeElement($etiquetaPasajero)) {
            // set the owning side to null (unless already changed)
            if ($etiquetaPasajero->getEtiqueta() === $this) {
                $etiquetaPasajero->setEtiqueta(null);
            }
        }

        return $this;
    }
}
