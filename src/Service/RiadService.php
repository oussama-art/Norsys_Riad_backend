<?php

namespace App\Service;

use App\Entity\Riad;
use App\Entity\RiadImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RiadService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createRiad(string $name, string $description, string $address, string $city, array $files = []): Riad
    {
        $riad = new Riad();
        $riad->setName($name);
        $riad->setDescription($description);
        $riad->setAddress($address);
        $riad->setCity($city);

        $this->entityManager->persist($riad);

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $image = new RiadImage();
                $image->setRiad($riad);
                $image->setImageFile($file);

                $this->entityManager->persist($image);
            }
        }

        $this->entityManager->flush();

        return $riad;
    }
}
