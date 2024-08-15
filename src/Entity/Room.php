<?php
// src/Entity/Room.php
namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\RoomController;


#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    normalizationContext: ['groups' => ['room:read']],
    denormalizationContext: ['groups' => ['room:write']],
    operations: [

        new GetCollection(
            uriTemplate: '/rooms/available/{checkIn}/{checkOut}/{guests}',
            controller: RoomController::class . '::findAvailableRoom',
            read: false,
            name: 'find_available_rooms',
        ),
    ]
)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['room:read', 'room:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['room:read', 'room:write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['room:read', 'room:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['room:read', 'room:write'])]
    private ?int $nb_personne = null;

    #[ORM\Column(type: 'float')]
    #[Groups(['room:read', 'room:write'])]
    private ?float $price = null;

    #[ORM\ManyToOne(targetEntity: Riad::class, inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['room:read', 'room:write'])]
    private ?Riad $riad = null;

    #[ORM\OneToMany(targetEntity: RoomImage::class, mappedBy: 'room', cascade: ['persist', 'remove'])]
    #[Groups(['room:read'])]
    private Collection $images;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'room')]
    #[Groups(['room:read'])]
    private Collection $reservations;



   

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->reservations = new ArrayCollection();

    }

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

    public function getNbPersonne(): ?int
    {
        return $this->nb_personne;
    }

    public function setNbPersonne(int $nb_personne): self
    {
        $this->nb_personne = $nb_personne;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getRiad(): ?Riad
    {
        return $this->riad;
    }

    public function setRiad(?Riad $riad): self
    {
        $this->riad = $riad;
        return $this;
    }

    /**
     * @return Collection<int, RoomImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(RoomImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setRoom($this);
        }

        return $this;
    }

    public function removeImage(RoomImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getRoom() === $this) {
                $image->setRoom(null);
            }
        }

        return $this;
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
            $reservation->setRoom($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getRoom() === $this) {
                $reservation->setRoom(null);
            }
        }

        return $this;
    }
}
