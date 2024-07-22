<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\RegistrationService;

class RegistrationController extends AbstractController
{
    private RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $decoded = json_decode($request->getContent(), true);

        // Proceed with registration service
        return $this->registrationService->registerUser($decoded);
    }
}
