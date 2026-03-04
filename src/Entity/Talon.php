<?php

namespace App\Entity;

use App\Repository\TalonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TalonRepository::class)
 */
class Talon
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Comprador::class)
     */
    private $Comprador;

    /**
     * @ORM\Column(type="smallint")
     */
    private $Numero;

    /**
     * @ORM\Column(type="date")
     */
    private $FechaSorteo;

    /**
     * @ORM\ManyToOne(targetEntity=Pasajero::class)
     */
    private $Pasajero;

    /**
     * @ORM\ManyToOne(targetEntity=Deposito::class)
     */
    private $Deposito;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Estado;

    /**
     * @ORM\Column(type="smallint")
     */
    private $Precio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $FechaRegistro;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $FechaEntrega;

    /**
     * @ORM\ManyToOne(targetEntity=Pasajero::class)
     */
    private $Solicitante;

    /**
     * @ORM\Column(type="smallint")
     */
    private $SorteoNumero;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $CreatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $UpdatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $DeletedAt;

    /**
     * @ORM\OneToMany(targetEntity=LinkPagoRifaTalones::class, mappedBy="Talon")
     */
    private $linkPagoRifaTalones;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Comentario;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $Recaudacion;

    /**
     * @ORM\ManyToOne(targetEntity=LoteRifa::class)
     */
    private $LoteRifa;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $valor;

    /**
     * @ORM\ManyToOne(targetEntity=Sorteo::class, inversedBy="talones")
     * @ORM\JoinColumn(nullable=true)
     */
    private $sorteo;

    public function __construct()
    {
        $this->linkPagoRifaTalones = new ArrayCollection();

        $this->setValor(20);
        $this->setRecaudacion(16);

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComprador(): ?Comprador
    {
        return $this->Comprador;
    }

    public function setComprador(?Comprador $Comprador): self
    {
        $this->Comprador = $Comprador;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->Numero;
    }

    public function setNumero(int $Numero): self
    {
        $this->Numero = $Numero;

        return $this;
    }

    public function getFechaSorteo(): ?\DateTimeInterface
    {
        return $this->FechaSorteo;
    }

    public function setFechaSorteo(\DateTimeInterface $FechaSorteo): self
    {
        $this->FechaSorteo = $FechaSorteo;

        return $this;
    }

    public function getPasajero(): ?Pasajero
    {
        return $this->Pasajero;
    }

    public function setPasajero(?Pasajero $Pasajero): self
    {
        $this->Pasajero = $Pasajero;

        return $this;
    }

    public function getDeposito(): ?Deposito
    {
        return $this->Deposito;
    }

    public function setDeposito(?Deposito $Deposito): self
    {
        $this->Deposito = $Deposito;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->Estado;
    }

    public function setEstado(string $Estado): self
    {
        $this->Estado = $Estado;

        return $this;
    }

    public function getPrecio(): ?int
    {
        return $this->Precio;
    }

    public function setPrecio(int $Precio): self
    {
        $this->Precio = $Precio;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->FechaRegistro;
    }

    public function setFechaRegistro(?\DateTimeInterface $FechaRegistro): self
    {
        $this->FechaRegistro = $FechaRegistro;

        return $this;
    }

    public function getFechaEntrega(): ?\DateTimeInterface
    {
        return $this->FechaEntrega;
    }

    public function setFechaEntrega(?\DateTimeInterface $FechaEntrega): self
    {
        $this->FechaEntrega = $FechaEntrega;

        return $this;
    }

    public function getSolicitante(): ?Pasajero
    {
        return $this->Solicitante;
    }

    public function setSolicitante(?Pasajero $Solicitante): self
    {
        $this->Solicitante = $Solicitante;

        return $this;
    }

    public function getSorteoNumero(): ?int
    {
        return $this->SorteoNumero;
    }

    public function setSorteoNumero(int $SorteoNumero): self
    {
        $this->SorteoNumero = $SorteoNumero;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $UpdatedAt): self
    {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->DeletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $DeletedAt): self
    {
        $this->DeletedAt = $DeletedAt;

        return $this;
    }

    public function getComentario(): ?string
    {
        return $this->Comentario;
    }

    public function setComentario(?string $Comentario): self
    {
        $this->Comentario = $Comentario;

        return $this;
    }

    public function getRecaudacion(): ?float
    {
        return $this->Recaudacion;
    }

    public function setRecaudacion(?float $Recaudacion): self
    {
        $this->Recaudacion = $Recaudacion;

        return $this;
    }

    public function getLoteRifa(): ?LoteRifa
    {
        return $this->LoteRifa;
    }

    public function setLoteRifa(?LoteRifa $LoteRifa): self
    {
        $this->LoteRifa = $LoteRifa;

        return $this;
    }

    public function getValor(): ?float
    {
        return $this->valor;
    }

    public function setValor(float $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getSorteo(): ?Sorteo
    {
        return $this->sorteo;
    }

    public function setSorteo(?Sorteo $sorteo): self
    {
        $this->sorteo = $sorteo;

        return $this;
    }
}
