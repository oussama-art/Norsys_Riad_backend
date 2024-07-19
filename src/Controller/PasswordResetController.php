<?php

namespace App\Controller;

namespace App\Controller;

use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PasswordResetController extends AbstractController
{
    private PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function reset(Request $request): JsonResponse
    {
        $token = $request->cookies->get('token');
        $credentials = json_decode($request->getContent(), true);
        $newPassword = $credentials['password'] ?? null;

        if (empty($token)) {
            return $this->json(['message' => 'Token is required.'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($newPassword)) {
            return $this->json(['message' => 'Password is required.'], Response::HTTP_BAD_REQUEST);
        }

        return $this->passwordResetService->resetPassword($token, $newPassword);
    }
}
