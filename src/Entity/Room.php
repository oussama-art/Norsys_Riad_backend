<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Riad;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ApiResource]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'integer')]
    private ?int $nb_personne = null;

    #[ORM\Column(type: 'float')]
    private ?float $price = null;

    #[ORM\ManyToOne(targetEntity: Riad::class, inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Riad $riad = null;

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNbPersonne(): ?int
    {
        return $this->nb_personne;
    }

    public function setNbPersonne(int $nb_personne): static
    {
        $this->nb_personne = $nb_personne;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getRiad(): ?Riad
    {
        return $this->riad;
    }

    public function setRiad(?Riad $riad): static
    {
        $this->riad = $riad;

        return $this;
    }
}
