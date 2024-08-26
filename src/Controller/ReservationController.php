<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

class ReservationController extends AbstractController
{
    #[Route('/api/reservation', methods: ['POST'])]
    public function createReservation(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $data = json_decode($request->getContent(), true);

        // Check for required fields
        $requiredFields = ['room_id', 'user_id', 'start_date', 'end_date', 'total_price'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $logger->error('Missing required field: ' . $field, ['data' => $data]);
                return new Response('Missing required fields', Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            $room = $entityManager->getRepository(Room::class)->find($data['room_id']);
            if (!$room) {
                $logger->error('Room not found', ['room_id' => $data['room_id']]);
                return new Response('Room not found', Response::HTTP_NOT_FOUND);
            }

            $user = $entityManager->getRepository(User::class)->find($data['user_id']);
            if (!$user) {
                $logger->error('User not found', ['user_id' => $data['user_id']]);
                return new Response('User not found', Response::HTTP_NOT_FOUND);
            }

            // Check if the room is available for the given date range
            $startDate = new \DateTime($data['start_date']);
            $endDate = new \DateTime($data['end_date']);

            $reservations = $entityManager->getRepository(Reservation::class)->findBy([
                'room' => $room
            ]);

            foreach ($reservations as $reservation) {
                if (($startDate < $reservation->getEndDate() && $endDate > $reservation->getStartDate())) {
                    $logger->error('Room is already reserved for the given dates', [
                        'room_id' => $data['room_id'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date']
                    ]);
                    return new Response('Room is already reserved for the given dates', Response::HTTP_CONFLICT);
                }
            }

            $reservation = new Reservation();
            $reservation->setFirstname($data['firstname']);  // Assumed required
            $reservation->setLastname($data['lastname']);    // Assumed required
            $reservation->setEmail($data['email']);          // Assumed required
            $reservation->setTel($data['tel']);              // Assumed required
            $reservation->setStartDate($startDate);
            $reservation->setEndDate($endDate);
            $reservation->setTotalPrice((float) $data['total_price']); // Cast to float
            $reservation->setDiscount(isset($data['discount']) ? (float) $data['discount'] : null); // Optional

            $reservation->setRoom($room);
            $reservation->setUser($user);

            $entityManager->persist($reservation);
            $entityManager->flush();

            return new Response('Reservation created successfully', Response::HTTP_CREATED);

        } catch (\Exception $e) {
            $logger->error('Error creating reservation', ['exception' => $e]);
            return new Response('An error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
