<?php

namespace App\Controller;

use App\Dto\ResetPasswordInput;
use App\Repository\TokenRepository;
use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ResetPasswordActionController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;
    private PasswordResetService $passwordResetService;
    private TokenRepository $tokenRepository;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        PasswordResetService $passwordResetService,
        TokenRepository $tokenRepository
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->passwordResetService = $passwordResetService;
        $this->tokenRepository = $tokenRepository;
    }

    #[Route('/update_password', name: 'update-password', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $credentials = json_decode($request->getContent(), true);
        $resetPasswordInput = $this->serializer->deserialize($credentials, ResetPasswordInput::class, 'json');

        // Validate the input data


        // Extract the token from the Authorization header
        $authorizationHeader = $request->headers->get('Authorization');
        if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return $this->json(['message' => 'Token not provided'], Response::HTTP_UNAUTHORIZED);
        }



        // Encode and set the new password
        return $this->passwordResetService->confirmPassword($matches[1], $resetPasswordInput);
    }
}
