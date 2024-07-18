<?php

namespace App\Controller;

use App\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PasswordResetService;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api', name: 'api_')]
class PasswordResetController extends AbstractController
{
    private PasswordResetService $passwordResetService;
    private EntityManagerInterface $entityManager;

    public function __construct(PasswordResetService $passwordResetService,EntityManagerInterface $entityManager)
    {
        $this->passwordResetService = $passwordResetService;
        $this->entityManager = $entityManager;
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

        try {
            // Call the service to request password reset
            $this->passwordResetService->requestPasswordReset($email);
        } catch (\Exception $e) {
            // Catch any exceptions and return an error response
            return $this->json(['message' => 'An error occurred while processing the request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return success message if no errors occurred
        return $this->json(['message' => 'If this email is registered, you will receive a password reset link.']);
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function reset(Request $request, EncryptionController $encryptionController): JsonResponse
    {
        $token = $request->cookies->get('encrypted_token');
        $credentials = json_decode($request->getContent(), true);

        if(empty($token)) {
            return $this->json(['message' => 'Token is required.']);
        }

        try {
            $decryptedToken = $encryptionController->decryptToken($token);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_BAD_REQUEST);
        }


        // Extract new password from request data
        $newPassword = $credentials['password'] ?? null;

        // Validate token and new password presence
        if (!$decryptedToken || !$newPassword) {
            return new JsonResponse(['message' => 'Token or password missing'], Response::HTTP_BAD_REQUEST);
        }

        // Retrieve token entity from repository
        $tokenEntity = $this->entityManager->getRepository(Token::class)->findOneBy(['token_name' => $decryptedToken]);

        // Check if token entity exists
        if (!$tokenEntity) {
            return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_NOT_FOUND);
        }

        // Check if token is expired
        if ($tokenEntity->isExpired()) {
            return new JsonResponse(['message' => 'Token expired'], Response::HTTP_BAD_REQUEST);
        }

        // Retrieve user associated with the token
        $user = $tokenEntity->getUser();

        // Reset the user's password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Remove the token from the database after successful password reset
        $this->entityManager->remove($tokenEntity);
        $this->entityManager->flush();

        // Return success response
        return new JsonResponse(['message' => 'Password reset successfully'], Response::HTTP_OK);
    }
}
