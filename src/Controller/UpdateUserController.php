<?php

// src/Controller/UpdateUserController.php

namespace App\Controller;

use App\Entity\User;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UpdateUserController extends AbstractController
{
    private RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        return $this->registrationService->updateUser($user->getId(), $data);
    }
}
