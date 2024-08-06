<?php
namespace App\Controller\Room;

use App\Entity\Room;
use App\Entity\RoomImage;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoomImageController extends AbstractController
{
    private EntityManagerInterface $em;
    private RoomRepository  $roomRepository;


    public function __construct(EntityManagerInterface $em, RoomRepository $roomRepository)
    {
        $this->em = $em;
        $this->roomRepository = $roomRepository;
    }

    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('imageFiles'); // Expect 'imageFiles' from form data
        $roomId = $request->request->get('id_riad'); // Retrieve the room ID

        if (!$file instanceof UploadedFile) {
            return new Response('No file uploaded', Response::HTTP_BAD_REQUEST);
        }

        if (!$roomId) {
            return new Response('Room ID is required', Response::HTTP_BAD_REQUEST);
        }

        $room = $this->roomRepository->find($roomId);

        if (!$room) {
            return new Response('Room not found', Response::HTTP_NOT_FOUND);
        }

        $image = new RoomImage();
        $image->setRoom($room);
        $image->setImageFile($file);

        try {
            $this->em->persist($image);
            $this->em->flush();
            return new Response(json_encode(['imageUrl' => $image->getImageUrl()]), Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
        } catch (FileException $e) {
            return new Response('File upload failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/rooom_images', name: 'upload_room_image', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        $imageFile = $request->files->get('imageFile');
        $roomId = $request->request->get('room');

        if (!$imageFile || !$roomId) {
            return new Response('No file or room ID provided', Response::HTTP_BAD_REQUEST);
        }

        $room = $this->em->getRepository(Room::class)->find($roomId);

        if (!$room) {
            throw new NotFoundHttpException('Room not found');
        }

        $roomImage = new RoomImage();
        $roomImage->setRoom($room);
        $roomImage->setImageFile($imageFile);

        $this->em->persist($roomImage);
        $this->em->flush();

        return new Response('Image uploaded successfully', Response::HTTP_OK);
    }

}
