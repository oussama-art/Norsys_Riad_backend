<?php

namespace App\Controller\Room;

use App\Entity\Riad;
use App\Entity\Room;
use App\Entity\RoomImage;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RoomController extends AbstractController
{
    private RoomRepository $roomRepository;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(RoomRepository $roomRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->roomRepository = $roomRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    

    #[Route('/roooms', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Handle non-file data
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $nbPersonne = $request->request->get('nb_personne');
        $price = $request->request->get('price');
        $idRiad = $request->request->get('id_riad');

        // Validate and create Room entity
        if (!$name || !$description || !$nbPersonne || !$price || !$idRiad) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        // Fetch the Riad entity
        $riad = $this->entityManager->getRepository(Riad::class)->find($idRiad);
        if (!$riad) {
            return new JsonResponse(['error' => 'Riad not found'], Response::HTTP_NOT_FOUND);
        }

        $room = new Room();
        $room->setName($name);
        $room->setDescription($description);
        $room->setNbPersonne($nbPersonne);
        $room->setPrice($price);
        $room->setRiad($riad); // Set the Riad entity

        $this->entityManager->persist($room);

        // Handle file uploads
        $files = $request->files->get('imageFiles');
        if ($files) {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $image = new RoomImage();
                    $image->setRoom($room);
                    $image->setImageFile($file);

                    $this->entityManager->persist($image);
                }
            }
        }

        $this->entityManager->flush();

        // Serialize and return the response
        $data = [
            'id' => $room->getId(),
            'name' => $room->getName(),
            'description' => $room->getDescription(),
            'nb_personne' => $room->getNbPersonne(),
            'price' => $room->getPrice(),
            // Optionally include additional data
        ];

        return new JsonResponse($data, Response::HTTP_CREATED);
    }
//    #[Route('/riads/{riadId}/rooms', methods: ['GET'])]
//    public function getRoomsByRiad(int $riadId): JsonResponse
//    {
//        // Fetch rooms by Riad ID
//        $rooms = $this->roomRepository->findBy(['riad' => $riadId]);
//
//        if (!$rooms) {
//            return new JsonResponse(['error' => 'No rooms found for this Riad'], Response::HTTP_NOT_FOUND);
//        }
//
//        // Normalize the room data for the response
//        $data = $this->serializer->normalize($rooms, null, ['groups' => 'room:read']);
//        return new JsonResponse($data);
//    }



}
