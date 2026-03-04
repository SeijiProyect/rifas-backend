<?php

namespace App\Entity;

use App\Repository\PersonaTokenFirebaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonaTokenFirebaseRepository::class)
 */
class PersonaTokenFirebase
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Persona::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $persona;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha_creado;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha_actualizado;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getFechaCreado(): ?\DateTimeInterface
    {
        return $this->fecha_creado;
    }

    public function setFechaCreado(?\DateTimeInterface $fecha_creado): self
    {
        $this->fecha_creado = $fecha_creado;

        return $this;
    }

    public function getFechaActualizado(): ?\DateTimeInterface
    {
        return $this->fecha_actualizado;
    }

    public function setFechaActualizado(?\DateTimeInterface $fecha_actualizado): self
    {
        $this->fecha_actualizado = $fecha_actualizado;

        return $this;
    }
}
