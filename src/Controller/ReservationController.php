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
        $data = json_decode($request->getContent(),
 true);

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

            if ($room->getReservation()) {
                $logger->error('Room already reserved', ['room_id' => $data['room_id']]);
                return new Response('Room already reserved', Response::HTTP_CONFLICT);
            }

            $user = $entityManager->getRepository(User::class)->find($data['user_id']);
            if (!$user) {
                $logger->error('User not found', ['user_id' => $data['user_id']]);
                return new Response('User not found', Response::HTTP_NOT_FOUND);
            }

            if ($user->getReservation()) {
                $logger->error('User already has a reservation', ['user_id' => $data['user_id']]);
                return new Response('User already has a reservation', Response::HTTP_CONFLICT);
            }

            $reservation = new Reservation();
            $reservation->setFirstname($data['firstname']);  // Assumed required
            $reservation->setLastname($data['lastname']);    // Assumed required
            $reservation->setEmail($data['email']);          // Assumed required
            $reservation->setTel($data['tel']);              // Assumed required
            $reservation->setStartDate(new \DateTime($data['start_date']));
            $reservation->setEndDate(new \DateTime($data['end_date']));
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



/*
namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/reservations', methods: ['POST'])]
    public function createReservation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        // Check for required fields
        if (!isset($data['room']) || !isset($data['user'])) {
            return new Response('Room and User are required', Response::HTTP_BAD_REQUEST);
        }

        try {
            $reservation = new Reservation();
            $reservation->setFirstname($data['firstname'] ?? null);
            $reservation->setLastname($data['lastname'] ?? null);
            $reservation->setEmail($data['email'] ?? null);
            $reservation->setTel($data['tel'] ?? null);
            $reservation->setStartDate(new \DateTime($data['start_date']));
            $reservation->setEndDate(new \DateTime($data['end_date']));
            $reservation->setTotalPrice($data['total_price']);
            $reservation->setDiscount($data['discount'] ?? null);

            // Find and set the room
            $room = $entityManager->getRepository(Room::class)->find($data['room']);
            if ($room) {
                $reservation->setRoom($room);
            } else {
                return new Response('Room not found', Response::HTTP_NOT_FOUND);
            }

            // Find and set the user
            $user = $entityManager->getRepository(User::class)->find($data['user']);
            if ($user) {
                $reservation->setUser($user);
            } else {
                return new Response('User not found', Response::HTTP_NOT_FOUND);
            }

            $entityManager->persist($reservation);
            $entityManager->flush();

            return new Response('Reservation created successfully', Response::HTTP_CREATED);

        } catch (\Exception $e) {
            // Log exception details for further analysis
            $this->get('logger')->error('Exception in ReservationController: ' . $e->getMessage());
            return new Response('An error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}

*/

/*
namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/reservations', methods: ['POST'])]
    public function createReservation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        // Check for required fields
        if (!isset($data['room_id']) || !isset($data['user_id'])) {
            return new Response('Room ID and User ID are required', Response::HTTP_BAD_REQUEST);
        }

        $reservation = new Reservation();
        $reservation->setFirstname($data['firstname'] ?? null);
        $reservation->setLastname($data['lastname'] ?? null);
        $reservation->setEmail($data['email'] ?? null);
        $reservation->setTel($data['tel'] ?? null);
        $reservation->setStartDate(new \DateTime($data['start_date']));
        $reservation->setEndDate(new \DateTime($data['end_date']));
        $reservation->setTotalPrice($data['total_price']);
        $reservation->setDiscount($data['discount'] ?? null);

        // Find and set the room
        $room = $entityManager->getRepository(Room::class)->find($data['room_id']);
        if ($room) {
            $reservation->setRoom($room);
        } else {
            return new Response('Room not found', Response::HTTP_NOT_FOUND);
        }

        // Find and set the user
        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        if ($user) {
            $reservation->setUser($user);
        } else {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }

        $entityManager->persist($reservation);
        $entityManager->flush();

        return new Response('Reservation created successfully', Response::HTTP_CREATED);
    }

}
*/
