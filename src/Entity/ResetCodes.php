<?php

namespace App\Entity;

use App\Repository\ResetCodesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResetCodesRepository::class)
 */
class ResetCodes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expired_at;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_used;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expired_at;
    }

    public function setExpiredAt(\DateTimeInterface $expired_at): self
    {
        $this->expired_at = $expired_at;

        return $this;
    }

    public function isIsUsed(): ?bool
    {
        return $this->is_used;
    }

    public function setIsUsed(bool $is_used): self
    {
        $this->is_used = $is_used;

        return $this;
    }
}
