<?php

namespace App\Entity;

use App\Repository\SorteoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SorteoRepository::class)
 */
class Sorteo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $sorteoNumero;

    /**
     * @ORM\Column(type="integer")
     */
    private $numeroInicialTalon;

    /**
     * @ORM\Column(type="integer")
     */
    private $numeroFinalTalon;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaSorteo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lugar;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $valorTalon;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $porcentajePremio;

    /**
     * @ORM\ManyToOne(targetEntity=Rifa::class, inversedBy="sorteos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $rifa;

    /**
     * @ORM\OneToMany(targetEntity=Talon::class, mappedBy="sorteo", orphanRemoval=true)
     */
    private $talones;

    public function __construct()
    {
        $this->talones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSorteoNumero(): ?int
    {
        return $this->sorteoNumero;
    }

    public function setSorteoNumero(int $sorteoNumero): self
    {
        $this->sorteoNumero = $sorteoNumero;

        return $this;
    }

    public function getNumeroInicialTalon(): ?int
    {
        return $this->numeroInicialTalon;
    }

    public function setNumeroInicialTalon(int $numeroInicialTalon): self
    {
        $this->numeroInicialTalon = $numeroInicialTalon;

        return $this;
    }

    public function getNumeroFinalTalon(): ?int
    {
        return $this->numeroFinalTalon;
    }

    public function setNumeroFinalTalon(int $numeroFinalTalon): self
    {
        $this->numeroFinalTalon = $numeroFinalTalon;

        return $this;
    }

    public function getFechaSorteo(): ?\DateTimeInterface
    {
        return $this->fechaSorteo;
    }

    public function setFechaSorteo(\DateTimeInterface $fechaSorteo): self
    {
        $this->fechaSorteo = $fechaSorteo;

        return $this;
    }

    public function getLugar(): ?string
    {
        return $this->lugar;
    }

    public function setLugar(?string $lugar): self
    {
        $this->lugar = $lugar;

        return $this;
    }

    public function getValorTalon(): ?float
    {
        return $this->valorTalon;
    }

    public function setValorTalon(?float $valorTalon): self
    {
        $this->valorTalon = $valorTalon;

        return $this;
    }

    public function getPorcentajePremio(): ?float
    {
        return $this->porcentajePremio;
    }

    public function setPorcentajePremio(?float $porcentajePremio): self
    {
        $this->porcentajePremio = $porcentajePremio;

        return $this;
    }

    public function getRifa(): ?Rifa
    {
        return $this->rifa;
    }

    public function setRifa(?Rifa $rifa): self
    {
        $this->rifa = $rifa;

        return $this;
    }

    /**
     * @return Collection|Talon[]
     */
    public function getTalones(): Collection
    {
        return $this->talones;
    }

    public function addTalone(Talon $talone): self
    {
        if (!$this->talones->contains($talone)) {
            $this->talones[] = $talone;
            $talone->setSorteo($this);
        }

        return $this;
    }

    public function removeTalone(Talon $talone): self
    {
        if ($this->talones->removeElement($talone)) {
            // set the owning side to null (unless already changed)
            if ($talone->getSorteo() === $this) {
                $talone->setSorteo(null);
            }
        }

        return $this;
    }
}
