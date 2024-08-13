<?php
// src/Controller/TokenController.php

namespace App\Controller\Token;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenController extends AbstractController
{
    private TokenStorageInterface $tokenStorage;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(TokenStorageInterface $tokenStorage, JWTTokenManagerInterface $jwtManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/validate-token', name: 'validate_token', methods: ['POST'])]
    public function validateToken(Request $request): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $admin = $request->request->get('admin', false);

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return new JsonResponse(['valid' => false, 'role' => null, 'expires_at' => null], 401);
        }

        $tokenString = substr($authorizationHeader, 7); // Extract token after 'Bearer '

        try {
            $payload = $this->jwtManager->parse($tokenString);

            $roles = $payload['roles'] ?? [];
            $isAdmin = in_array('ROLE_ADMIN', $roles, true);

            $expirationTime = $payload['exp'] ?? null;
            if ($expirationTime) {
                // Convert the expiration timestamp to a readable date format (optional)
                $expirationTime = (new \DateTime())->setTimestamp($expirationTime)->format(\DateTime::ATOM);

                // Check if token is expired
                if ($expirationTime < (new \DateTime())->format(\DateTime::ATOM)) {
                    return new JsonResponse(['valid' => false, 'role' => null, 'expires_at' => $expirationTime], 401);
                }
            }

            if ($admin && !$isAdmin) {
                return new JsonResponse([
                    'valid' => false,
                    'role' => 'ROLE_USER',
                    'expires_at' => $expirationTime
                ], 403);
            }

            return new JsonResponse([
                'valid' => true,
                'role' => $isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER',
                'expires_at' => $expirationTime
            ], 200);
        } catch (JWTDecodeFailureException $e) {
            return new JsonResponse(['valid' => false, 'role' => null, 'expires_at' => null], 401);
        }
    }


    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !$token->getUser() instanceof UserInterface) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $user = $token->getUser();

        if (method_exists($user, 'getId') && method_exists($user, 'getUsername') && method_exists($user, 'getEmail')) {
            $userData = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ];
            return new JsonResponse($userData);
        }

        return new JsonResponse(['error' => 'User data is incomplete or invalid'], 400);
    }
}
