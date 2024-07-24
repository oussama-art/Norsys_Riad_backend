<?php
// src/Entity/User.php

// src/Entity/User.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            uriTemplate: '/register',
            controller: 'App\Controller\RegistrationController::index',
            openapiContext: [
                'summary' => 'Registers a new user.',
                'description' => 'Registers a new user with email, username, and password.'
            ]
        ),
        new Patch(),
        new Delete(),
        new Put()
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Email should not be blank.')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email address.')]
    private ?string $email;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Length(
        min: 10,
        minMessage: 'Your password must be at least {{ limit }} characters long.'
    )]
    #[Assert\NotBlank(message: 'Username should not be blank.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).*$/',
        message: 'Your username must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.'
    )]
    private ?string $username;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Password should not be blank.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Your password must be at least {{ limit }} characters long.'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).*$/',
        message: 'Your password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.'
    )]
    private ?string $password;

    #[ORM\Column(type: 'json')]
    #[Assert\NotBlank(message: 'Role should not be blank.')]
    private array $roles;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $lastPasswordChangedAt = null;

    /**
     * @var Collection<int, Token>
     */
    #[ORM\OneToMany(targetEntity: Token::class, mappedBy: 'user')]
    private Collection $tokens;

    public function __construct()
    {
        $this->tokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getLastPasswordChangedAt(): ?DateTimeInterface
    {
        return $this->lastPasswordChangedAt;
    }

    public function setLastPasswordChangedAt(?DateTimeInterface $lastPasswordChangedAt): static
    {
        $this->lastPasswordChangedAt = $lastPasswordChangedAt;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getTokens(): Collection
    {
        return $this->tokens;
    }
}

