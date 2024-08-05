<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\RoomReserved;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/api/reservations', name: 'create_reservation', methods: ['POST'])]
    public function createReservation(Request $request, EntityManagerInterface $em, RoomRepository $roomRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create a new reservation entity
        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTime($data['startDate']));
        $reservation->setEndDate(new \DateTime($data['endDate']));
        $reservation->setUserId($data['userId']);
        $reservation->setDiscount($data['discount']);

        // Add rooms to the reservation
        foreach ($data['rooms'] as $roomData) {
            $room = $roomRepository->find($roomData['id']);
            if ($room) {
                $roomReserved = new RoomReserved();
                $roomReserved->setRoom($room);
                $roomReserved->setPrice($roomData['price']);
                $reservation->addRoomReserved($roomReserved);
            }
        }

        // Calculate total price
        $reservation->setTotalPrice($reservation->calculateTotalPrice());

        // Persist and flush the reservation
        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['status' => 'Reservation created!'], JsonResponse::HTTP_CREATED);
    }
}
