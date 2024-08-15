<?php

namespace App\Controller;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable; // Ensure this is from the global namespace


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

    #[Route('/api/roooms', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $rooms = $this->roomRepository->findAll();
        $data = $this->serializer->normalize($rooms, null, ['groups' => 'room:read']);
        return new JsonResponse($data);
    }

    #[Route('/api/roooms', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $room = new Room();
        $room->setName($data['name']);
        $room->setDescription($data['description']);
        $room->setNbPersonne($data['nb_personne']);
        $room->setPrice($data['price']);
        // Optionally handle related entity like Riad
        // $riad = $this->getRiad($data['riad_id']);
        // $room->setRiad($riad);

        $this->entityManager->persist($room);
        $this->entityManager->flush();

        $data = $this->serializer->normalize($room, null, ['groups' => 'room:read']);
        return new JsonResponse($data, Response::HTTP_CREATED);
    }
    
    #[Route('/rooms/available/{checkIn}/{checkOut}/{guests}', name: 'find_available_rooms', methods: ['GET'])]
    public function findAvailableRoom(RoomRepository $roomRepository, SerializerInterface $serializer, \DateTimeImmutable $checkIn, \DateTimeImmutable $checkOut, int $guests): Response
    {
        $availableRooms = $roomRepository->findChambresDisponibles($checkIn, $checkOut, $guests);

        $jsonContent = $serializer->serialize($availableRooms, 'json', ['groups' => 'room:read']);

        return new Response($jsonContent, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/rooms/{id}/reservations', name: 'get_room_reservations', methods: ['GET'])]
    public function getRoomReservations(int $id, ReservationRepository $reservationRepository, SerializerInterface $serializer): JsonResponse
    {
        $today = new DateTimeImmutable();
        $reservations = $reservationRepository->findReservationsByRoomAndDate($id, $today);

        $data = $serializer->serialize($reservations, 'json', ['groups' => 'reservation:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

}
