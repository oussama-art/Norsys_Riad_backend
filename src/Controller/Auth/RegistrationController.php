<?php

namespace App\Controller\Auth;

use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
