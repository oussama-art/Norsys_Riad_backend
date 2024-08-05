<?php

// src/Service/AdminService.php

namespace App\Service\Admin;

use App\Entity\User;
use App\Service\RegistrationService;
use App\Service\TokenService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminService
{
    private RegistrationService $registrationService;
    private UserPasswordHasherInterface $passwordEncoder;

    private TokenService  $tokenService;
    private ManagerRegistry $doctrine;


    public function __construct(
        ManagerRegistry $doctrine,RegistrationService $registrationService,
                                UserPasswordHasherInterface $passwordEncoder,TokenService  $tokenService)
    {
        $this->registrationService = $registrationService;
        $this->passwordEncoder = $passwordEncoder;
        $this->doctrine = $doctrine;
        $this->tokenService = $tokenService;
    }

    public function registerAdmin(array $userData): JsonResponse
    {
        // Ensure the roles are included in the userData
        if (!isset($userData['roles']) || !in_array('ROLE_ADMIN', $userData['roles'])) {
            return new JsonResponse(['error' => 'Admin role is required.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Call the RegistrationService to handle user registration
        return $this->registrationService->registerUser($userData);
    }

    public function login(array $credentials): JsonResponse
    {
        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$username || !$password) {
            return new JsonResponse(['error' => 'Username and password are required.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $allowedFields = ['username', 'password'];

        foreach (array_keys($credentials) as $key) {
            if (!in_array($key, $allowedFields)) {
                return new JsonResponse(['message' => sprintf('Unallowed field %s detected: only username and password required ', $key)], Response::HTTP_BAD_REQUEST);
            }
        }

        $user = $this->doctrine->getManager()->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found', 'status' => Response::HTTP_NOT_FOUND]);
        }

        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Access denied', 'status' => Response::HTTP_FORBIDDEN]);
        }

        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Password Incorrect', 'status' => Response::HTTP_UNAUTHORIZED]);
        }

        $tokenEntity = $this->tokenService->createToken($user);

        return new JsonResponse([
            'message' => 'Login successfully',
            'token' => $tokenEntity->getTokenName(),
            'status' => Response::HTTP_OK
        ]);
    }

}
