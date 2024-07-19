<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\RegistrationService;
use App\Entity\User;

class RegistrationController extends AbstractController
{
    private RegistrationService $registrationService;
    private ValidatorInterface $validator;

    public function __construct(RegistrationService $registrationService, ValidatorInterface $validator)
    {
        $this->registrationService = $registrationService;
        $this->validator = $validator;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $decoded = json_decode($request->getContent(), true);

        // Validate fields
        $email = $decoded['email'] ?? '';
        $plaintextPassword = $decoded['password'] ?? '';
        $username = $decoded['username'] ?? '';

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($plaintextPassword);
        $user->setUsername($username);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        // If validation passes, proceed with registration service
        return $this->registrationService->registerUser($email, $plaintextPassword, $username);
    }
}
