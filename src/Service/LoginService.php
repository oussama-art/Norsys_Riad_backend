<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginService
{
    private UserPasswordHasherInterface $passwordEncoder;
    private TokenService $tokenService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        TokenService $tokenService,
        EntityManagerInterface $entityManager
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenService = $tokenService;
        $this->entityManager = $entityManager;
    }

    public function login(string $username, string $password): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found', 'status' => 404]);
        }

        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Invalid credentials', 'status' => 401]);
        }

        $tokenEntity = $this->tokenService->createToken($user);
        $user->setloginTime(new \DateTime());
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
