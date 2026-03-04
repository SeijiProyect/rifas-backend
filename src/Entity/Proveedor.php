<?php

namespace App\Entity;

use App\Repository\ProveedorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProveedorRepository::class)
 */
class Proveedor
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
    private $contacto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $forma_contacto;

    /**
     * @ORM\ManyToOne(targetEntity=Pais::class)
     */
    private $pais;

    /**
     * @ORM\ManyToOne(targetEntity=Ciudad::class)
     */
    private $ciudad;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $whatsapp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebook;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telefonos;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mails;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $cuenta_bancaria;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comentarios;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $direccion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $google_maps;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $maps_me;

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

    public function getContacto(): ?string
    {
        return $this->contacto;
    }

    public function setContacto(string $contacto): self
    {
        $this->contacto = $contacto;

        return $this;
    }

    public function getFormaContacto(): ?string
    {
        return $this->forma_contacto;
    }

    public function setFormaContacto(?string $forma_contacto): self
    {
        $this->forma_contacto = $forma_contacto;

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

    public function getCiudad(): ?Ciudad
    {
        return $this->ciudad;
    }

    public function setCiudad(?Ciudad $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): self
    {
        $this->whatsapp = $whatsapp;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getTelefonos(): ?string
    {
        return $this->telefonos;
    }

    public function setTelefonos(?string $telefonos): self
    {
        $this->telefonos = $telefonos;

        return $this;
    }

    public function getMails(): ?string
    {
        return $this->mails;
    }

    public function setMails(?string $mails): self
    {
        $this->mails = $mails;

        return $this;
    }

    public function getCuentaBancaria(): ?string
    {
        return $this->cuenta_bancaria;
    }

    public function setCuentaBancaria(?string $cuenta_bancaria): self
    {
        $this->cuenta_bancaria = $cuenta_bancaria;

        return $this;
    }

    public function getComentarios(): ?string
    {
        return $this->comentarios;
    }

    public function setComentarios(?string $comentarios): self
    {
        $this->comentarios = $comentarios;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        $this->direccion = $direccion;

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

    public function getMapsMe(): ?string
    {
        return $this->maps_me;
    }

    public function setMapsMe(?string $maps_me): self
    {
        $this->maps_me = $maps_me;

        return $this;
    }
}
