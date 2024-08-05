<?php

namespace App\Controller;

use App\Entity\Riad;
use App\Entity\RiadImage;
use App\Repository\RiadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

class RiadController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private RiadRepository $riadRepository;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, RiadRepository $riadRepository)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }
    #[Route('/riiads', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $riads = $this->riadRepository->findAll();
        $data = $this->serializer->normalize($riads, null, ['groups' => 'riad:read']);
        return new JsonResponse($data);
    }

    #[Route('/riiads', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Handle non-file data
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $address = $request->request->get('address');
        $city = $request->request->get('city');

        // Validate and create Riad entity
        if (!$name || !$description || !$address || !$city) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $riad = new Riad();
        $riad->setName($name);
        $riad->setDescription($description);
        $riad->setAddress($address);
        $riad->setCity($city);

        $this->entityManager->persist($riad);

        // Handle file uploads
        $files = $request->files->get('imageFiles');
        if ($files) {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $image = new RiadImage();
                    $image->setRiad($riad);
                    $image->setImageFile($file);

                    $this->entityManager->persist($image);
                }
            }
        }

        $this->entityManager->flush();

        // Serialize and return the response
        $data = [
            'id' => $riad->getId(),
            'name' => $riad->getName(),
            'description' => $riad->getDescription(),
            'address' => $riad->getAddress(),
            'city' => $riad->getCity(),
            // Optionally include additional data
        ];

        return new JsonResponse($data, Response::HTTP_CREATED);
    }
}
