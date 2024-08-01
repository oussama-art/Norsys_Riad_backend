<?php

namespace App\Controller;

use App\Dto\UpdatePasswordInput;
use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordResetController extends AbstractController
{
    private PasswordResetService $passwordResetService;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, PasswordResetService $passwordResetService, ValidatorInterface $validator)
    {
        $this->passwordResetService = $passwordResetService;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function reset(Request $request): JsonResponse
    {
        $content = $request->getContent();

        if (empty($content)) {
            return $this->json(['message' => 'Request content is empty.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Deserialize JSON string into UpdatePasswordInput DTO
            $updatePasswordInput = $this->serializer->deserialize($content, UpdatePasswordInput::class, 'json');
        } catch (\Exception) {
            return $this->json(['message' => 'Invalid data format.'], Response::HTTP_BAD_REQUEST);
        }

        // Retrieve token from cookies
        $token = $request->cookies->get('token');

        if (empty($token)) {
            return $this->json(['message' => 'Token is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Validate the password input using the Validator component
        $errors = $this->validator->validate($updatePasswordInput);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['message' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Use the service to reset the password
        return $this->passwordResetService->resetPassword($token, $updatePasswordInput);
    }
}
