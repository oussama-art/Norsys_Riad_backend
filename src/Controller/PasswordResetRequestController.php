<?php

namespace App\Controller;


use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PasswordResetService;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetRequestController extends AbstractController
{
    private PasswordResetService $passwordResetService;


    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;

    }

    #[Route('/password-reset-request', name: 'password_reset_request', methods: ['POST'])]
    public function request(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate if email exists in the request data
        if (empty($data['email'])) {
            return $this->json(['message' => 'Email is required.'], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];


            // Call the service to request password reset
        return $this->passwordResetService->requestPasswordReset($email);

    }


}
