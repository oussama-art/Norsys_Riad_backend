<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'token_name', length: 1024)]
    private ?string $token_name = null;

    #[ORM\ManyToOne(inversedBy: 'tokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean')]
    private bool $expired = false;

    public function __construct()
    {
        $this->expired = false; // Set default value in constructor if not using ORM default
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTokenName(): ?string
    {
        return $this->token_name;
    }

    public function setTokenName(string $token_name): static
    {
        $this->token_name = $token_name;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): static
    {
        $this->expired = $expired;
        return $this;
    }
}
