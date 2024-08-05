<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[Vich\Uploadable]
#[ApiResource(
    normalizationContext: ['groups' => ['riad_image:read']],
    denormalizationContext: ['groups' => ['riad_image:write']]
)]
class RiadImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['riad:read', 'riad_image:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['riad:read', 'riad_image:read', 'riad_image:write'])]
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

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): self
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
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
