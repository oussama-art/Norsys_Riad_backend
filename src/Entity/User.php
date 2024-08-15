<?php
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
        minMessage: 'Your username must be at least {{ limit }} characters long.'
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
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $lastPasswordChangedAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'First name should not be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'First name cannot be longer than {{ limit }} characters.'
    )]
    private ?string $firstname= null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Second name should not be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Second name cannot be longer than {{ limit }} characters.'
    )]
    private ?string $secondname = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'CIN should not be blank.')]
    #[Assert\Length(
        max: 20,
        maxMessage: 'CIN must be at most {{ limit }} characters long.'
    )]
    private ?string $cin = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Address should not be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Address cannot be longer than {{ limit }} characters.'
    )]
    private ?string $address = null;

    #[ORM\Column(length: 15)]
    #[Assert\NotBlank(message: 'Telephone number should not be blank.')]
    #[Assert\Regex(
        pattern: '/^\+?\d{10,15}$/',
        message: 'Telephone number must be between 10 and 15 digits, and can include a leading +.'
    )]
    private ?string $tele = null;

    #[ORM\OneToMany(targetEntity: Token::class, mappedBy: 'user', cascade: ['remove'], orphanRemoval: true)]
    private Collection $tokens;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user')]
    private Collection $reservations;

    public function __construct()
    {
        $this->tokens = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    // Getter and setter methods for the new properties

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getSecondname(): ?string
    {
        return $this->secondname;
    }

    public function setSecondname(string $secondname): self
    {
        $this->secondname = $secondname;

        return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): self
    {
        $this->cin = $cin;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getTele(): ?string
    {
        return $this->tele;
    }

    public function setTele(string $tele): self
    {
        $this->tele = $tele;

        return $this;
    }

    // Existing getter and setter methods for other properties...

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

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }
}
