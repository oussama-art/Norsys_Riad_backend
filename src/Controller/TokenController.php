<?php

// src/Controller/TokenController.php

namespace App\Controller;

use App\Repository\TokenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenController extends AbstractController
{
    private TokenStorageInterface $tokenStorage;
    private TokenRepository $tokenRepository;

    public function __construct(TokenStorageInterface $tokenStorage, TokenRepository $tokenRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->tokenRepository = $tokenRepository;
    }

    #[Route('/validate-token', name: 'validate_token', methods: ['POST'])]
    public function validateToken(Request $request): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $admin = $request->request->get('admin', false);

        if (!$authorizationHeader) {
            return new JsonResponse(['valid' => false, 'role' => null], 401);
        }

        try {
            $token = $this->tokenStorage->getToken();

            if ($token instanceof TokenInterface) {
                $user = $token->getUser();

                // Ensure the user is authenticated and not an anonymous user
                if ($user instanceof UserInterface) {
                    // Extract roles, assuming $user->getRoles() returns an array of roles
                    $roles = $user->getRoles();

                    // Check if the user has an admin role
                    $isAdmin = in_array('ROLE_ADMIN', $roles, true);

                    // Determine if the request is for admin validation and the user is not an admin
                    if ($admin && !$isAdmin) {
                        return new JsonResponse(['valid' => false, 'role' => 'ROLE_USER'], 403);
                    }

                    return new JsonResponse(['valid' => true, 'role' => $isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER'], 200);
                } else {
                    return new JsonResponse(['valid' => false, 'role' => null], 401);
                }
            } else {
                return new JsonResponse(['valid' => false, 'role' => null], 401);
            }
        } catch (AuthenticationException $e) {
            return new JsonResponse(['valid' => false, 'role' => null], 401);
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

        // Customize the response to include only necessary details
        $userData = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            // Add other user details as needed
        ];

        return new JsonResponse($userData);
    }
}
