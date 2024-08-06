<?php
// src/Entity/RoomImage.php
namespace App\Entity;

use ApiPlatform\Metadata\GetCollection;
use App\Controller\RoomImageController;
use App\Repository\RoomImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RoomImageRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/room_images',
            controller: RoomImageController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: false,
            deserialize: false
        ),
        new GetCollection(uriTemplate: '/room_images'),
        new Get(uriTemplate: '/room_images/{id}'),
        new Patch(),
        new Delete()
    ]
)]
class RoomImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['room:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['room:read'])]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'room_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function setImageFile(?File $imageFile = null): self
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[Groups(['room:read'])]
    public function getImageUrl(): ?string
    {
        return $this->imageName ? '/images/rooms/' . $this->imageName : null;
    }
}
