<?php

namespace App\Entity;

use App\Repository\LinkPagoRifaTalonesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinkPagoRifaTalonesRepository::class)
 */
class LinkPagoRifaTalones
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=LinkPagoRifa::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $LinkPagoRifa;

    /**
     * @ORM\ManyToOne(targetEntity=Talon::class, inversedBy="linkPagoRifaTalones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Talon;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLinkPagoRifa(): ?LinkPagoRifa
    {
        return $this->LinkPagoRifa;
    }

    public function setLinkPagoRifa(?LinkPagoRifa $LinkPagoRifa): self
    {
        $this->LinkPagoRifa = $LinkPagoRifa;

        return $this;
    }

    public function getTalon(): ?Talon
    {
        return $this->Talon;
    }

    public function setTalon(Talon $Talon): self
    {
        $this->Talon = $Talon;

        return $this;
    }
}
