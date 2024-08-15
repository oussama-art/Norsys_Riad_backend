<?php
// src/Entity/RiadImage.php
namespace App\Entity;

use ApiPlatform\Metadata\GetCollection;
use App\Controller\RiadImageController;
use App\Repository\RiadImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RiadImageRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/riad_images',
            controller: RiadImageController::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: false,
            deserialize: false
        ),
        new GetCollection(uriTemplate: '/riad_images'),
        new Get(uriTemplate: '/riad_images/{id}'),
        new Patch(),
        new Delete()
    ]
)]
class RiadImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['riad:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['riad:read'])]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'riad_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\ManyToOne(targetEntity: Riad::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Riad $riad = null;

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

    public function getRiad(): ?Riad
    {
        return $this->riad;
    }

    public function setRiad(?Riad $riad): self
    {
        $this->riad = $riad;
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

    #[Groups(['riad:read'])]
    public function getImageUrl(): ?string
    {
        return $this->imageName ? '/images/riads/' . $this->imageName : null;
    }
}
