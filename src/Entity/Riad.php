<?php
// src/Entity/Riad.php

namespace App\Entity;

use App\Repository\RiadRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RiadRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['riad:read']],
    denormalizationContext: ['groups' => ['riad:write']]
)]
class Riad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['riad:read', 'riad:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['riad:read', 'riad:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['riad:read', 'riad:write'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['riad:read', 'riad:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups(['riad:read', 'riad:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $city = null;

    #[ORM\OneToMany(targetEntity: Room::class, mappedBy: 'riad', orphanRemoval: true)]
    #[Groups(['riad:read'])]
    private Collection $rooms;

    #[ORM\OneToMany(targetEntity: RiadImage::class, mappedBy: 'riad', orphanRemoval: true)]
    #[Groups(['riad:read'])]
    private Collection $images;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    // Getter and setter methods...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setRiad($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getRiad() === $this) {
                $room->setRiad(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RiadImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(RiadImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setRiad($this);
        }

        return $this;
    }

    public function removeImage(RiadImage $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getRiad() === $this) {
                $image->setRiad(null);
            }
        }

        return $this;
    }
}

