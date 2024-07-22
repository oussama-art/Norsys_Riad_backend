<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginService
{
    private UserPasswordHasherInterface $passwordEncoder;
    private TokenService $tokenService;

    private ManagerRegistry $doctrine;

    public function __construct(
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordEncoder,
        TokenService $tokenService

    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenService = $tokenService;

        $this->doctrine = $doctrine;
    }

    public function login(array $credentials): JsonResponse
    {
        $em = $this->doctrine->getManager();
        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;

        $allowedFields = ['username', 'password'];

        foreach (array_keys($credentials) as $key) {
            if (!in_array($key, $allowedFields)) {
                return new JsonResponse(['message' => sprintf('Unallowed field %s detected: only username and password required ', $key)], Response::HTTP_BAD_REQUEST);
            }
        }

        if (!$username || !$password) {
            return new JsonResponse(['message' => 'Missing username or password'], Response::HTTP_BAD_REQUEST);
        }
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found', 'status' => 404]);
        }

        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Password Incorrect', 'status' => 401]);
        }

        $tokenEntity = $this->tokenService->createToken($user);

        return new JsonResponse(['message'=>'Login successfully','token' => $tokenEntity->getTokenName(), 'status' => 200]);
    }

    public function logout(string $token): ?array
    {
        $tokenEntity = $this->tokenService->invalidateToken($token);

        if (!$tokenEntity) {
            return ['message' => 'Token not found', 'status' => 404];
        }

        return ['message' => 'Logout successful', 'status' => 200];
    }
}
