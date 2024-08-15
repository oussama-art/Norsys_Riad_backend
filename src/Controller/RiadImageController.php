<?php
namespace App\Controller;

use App\Entity\RiadImage;
use App\Repository\RiadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RiadImageController extends AbstractController
{
    private $em;
    private $riadRepository;

    public function __construct(EntityManagerInterface $em, RiadRepository $riadRepository)
    {
        $this->em = $em;
        $this->riadRepository = $riadRepository;
    }

    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('imageFile');
        $riadId = $request->request->get('riad');

        if (!$file instanceof UploadedFile) {
            return new Response('No file uploaded', Response::HTTP_BAD_REQUEST);
        }

        if (!$riadId) {
            return new Response('Riad ID is required', Response::HTTP_BAD_REQUEST);
        }

        $riad = $this->riadRepository->find($riadId);

        if (!$riad) {
            return new Response('Riad not found', Response::HTTP_NOT_FOUND);
        }

        $image = new RiadImage();
        $image->setRiad($riad);
        $image->setImageFile($file);

        try {
            $this->em->persist($image);
            $this->em->flush();
            return new Response(json_encode(['imageUrl' => $image->getImageUrl()]), Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
        } catch (FileException $e) {
            return new Response('File upload failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
