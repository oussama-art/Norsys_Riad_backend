<?php
namespace App\Controller;

use App\Entity\Riad;
use App\Entity\RiadImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RiadImageController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/riiad_images', name: 'upload_riad_image', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        // Debugging output
        error_log('Request data: ' . print_r($request->request->all(), true));
        error_log('Files data: ' . print_r($request->files->all(), true));

        $imageFile = $request->files->get('imageFile');
        $riadId = $request->request->get('riad');

        if (!$imageFile || !$imageFile instanceof UploadedFile) {
            return new Response('No valid file uploaded', Response::HTTP_BAD_REQUEST);
        }

        if (!$riadId) {
            return new Response('Riad ID is required', Response::HTTP_BAD_REQUEST);
        }

        $riad = $this->em->getRepository(Riad::class)->find($riadId);

        if (!$riad) {
            throw new NotFoundHttpException('Riad not found');
        }

        $riadImage = new RiadImage();
        $riadImage->setRiad($riad);
        $riadImage->setImageFile($imageFile);

        try {
            $this->em->persist($riadImage);
            $this->em->flush();
            return new Response(json_encode(['imageUrl' => $riadImage->getImageUrl()]), Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
        } catch (FileException $e) {
            return new Response('File upload failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

