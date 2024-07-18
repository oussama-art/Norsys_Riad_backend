<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Token;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api', name: 'api_')]
class LoginController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 300; // 5 minutes

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/login', name: 'login_check', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordEncoder, ManagerRegistry $doctrine, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        $credentials = json_decode($request->getContent(), true);
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['message' => 'Missing email or password'], Response::HTTP_BAD_REQUEST);
        }

        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($this->isAccountLocked($user)) {
            return $this->json(['message' => 'Account is locked due to too many failed login attempts. Try again later.'], Response::HTTP_FORBIDDEN);
        }

        if (!$passwordEncoder->isPasswordValid($user, $password)) {
            $this->incrementFailedLoginAttempts($user);
            return $this->json(['message' => 'Password incorrect'], Response::HTTP_UNAUTHORIZED);
        }

        $this->resetFailedLoginAttempts($user);

        $token = $JWTManager->create($user);

        $tokenEntity = new Token();
        $tokenEntity->setTokenName($token);
        $tokenEntity->setUser($user);
        $tokenEntity->setExpired(false);

        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        return $this->json(['token' => $token]);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader === null || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(['message' => 'Token not provided'], Response::HTTP_BAD_REQUEST);
        }

        $token = substr($authHeader, 7);

        $tokenEntity = $doctrine->getRepository(Token::class)->findOneBy(['tokenName' => $token]);

        if (!$tokenEntity) {
            return $this->json(['message' => 'Token not found'], Response::HTTP_NOT_FOUND);
        }

        $tokenEntity->setExpired(true);
        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        return $this->json(['message' => 'Logout successful'], Response::HTTP_OK);
    }

    private function isAccountLocked(User $user): bool
    {
        if ($user->getLockoutTime() === null) {
            return false;
        }

        $lockoutDuration = new \DateInterval('PT' . self::LOCKOUT_DURATION . 'S');
        $lockoutEndTime = (clone $user->getLockoutTime())->add($lockoutDuration);

        if (new \DateTime() > $lockoutEndTime) {
            $user->setLockoutTime(null);
            $this->entityManager->flush();
            return false;
        }

        return true;
    }

    private function incrementFailedLoginAttempts(User $user): void
    {
        $user->incrementFailedLoginAttempts();
        if ($user->getFailedLoginAttempts() >= self::MAX_FAILED_ATTEMPTS) {
            $user->setLockoutTime(new \DateTime());
        }
        $this->entityManager->flush();
    }

    private function resetFailedLoginAttempts(User $user): void
    {
        $user->resetFailedLoginAttempts();
        $user->setLockoutTime(null);
        $this->entityManager->flush();
    }
}
