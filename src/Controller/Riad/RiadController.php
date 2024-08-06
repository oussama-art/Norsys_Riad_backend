<?php

// src/Controller/Riad/RiadController.php

namespace App\Controller\Riad;

use App\Service\RiadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RiadController extends AbstractController
{
    private RiadService $riadService;

    public function __construct(RiadService $riadService)
    {
        $this->riadService = $riadService;
    }

    #[Route('/riiads', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $address = $request->request->get('address');
        $city = $request->request->get('city');

        if (!$name || !$description || !$address || !$city) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $files = $request->files->get('imageFiles');
        $riad = $this->riadService->createRiad($name, $description, $address, $city, $files);

        $data = [
            'id' => $riad->getId(),
            'name' => $riad->getName(),
            'description' => $riad->getDescription(),
            'address' => $riad->getAddress(),
            'city' => $riad->getCity(),
        ];

        return new JsonResponse($data, Response::HTTP_CREATED);
    }
}

