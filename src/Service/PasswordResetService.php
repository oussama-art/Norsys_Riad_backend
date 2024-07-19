<?php

namespace App\Service;

use App\Entity\Token;
use App\Entity\User;
use App\Message\Messagetype\SendPasswordResetLinkMessage;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

    class PasswordResetService
{
    private ManagerRegistry $doctrine;

    private TokenService $tokenService;
    private MessageBusInterface $bus; // Add MessageBusInterface for dispatching messages

    public function __construct(
        ManagerRegistry $doctrine,
        MessageBusInterface $bus, // Inject MessageBusInterface
        TokenService $tokenService // Inject TokenService
    )
    {
        $this->doctrine = $doctrine;
        $this->tokenService = $tokenService; // Assign TokenService
        $this->bus = $bus; // Assign MessageBusInterface
    }

    public function requestPasswordReset(string $email): void
    {
        $em = $this->doctrine->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }
        $tokenEntity = $this->tokenService->createToken($user);
        $this->bus->dispatch(new SendPasswordResetLinkMessage($email, $user->getUsername(), $tokenEntity->getTokenName()));
    }

    public function resetPassword(string $token, string $newPassword): JsonResponse
    {
        $em = $this->doctrine->getManager();
        $tokenEntity = $em->getRepository(Token::class)->findOneBy(['token_name' => $token]);

        if (!$tokenEntity) {
            return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_NOT_FOUND);
        }

        if ($tokenEntity->isExpired()) {
            return new JsonResponse(['message' => 'Token expired'], Response::HTTP_BAD_REQUEST);
        }

        $user = $tokenEntity->getUser();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->remove($tokenEntity);
        $em->flush();

        return new JsonResponse(['message' => 'Password reset successfully'], Response::HTTP_OK);
    }
}
