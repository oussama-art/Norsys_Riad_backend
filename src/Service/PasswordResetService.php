<?php

// src/Service/PasswordResetService.php

namespace App\Service;

use App\Dto\ResetPasswordInput;
use App\Dto\UpdatePasswordInput;
use App\Entity\Token;
use App\Entity\User;
use App\Message\Messagetype\SendPasswordChangeConfirmationMessage;
use App\Message\Messagetype\SendPasswordResetLinkMessage;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;

class PasswordResetService
{
    private ManagerRegistry $doctrine;
    private TokenService $tokenService;
    private MessageBusInterface $bus;
    private TokenRepository $tokenRepository;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordEncoder;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $doctrine,
        MessageBusInterface $bus,
        TokenService $tokenService,
        UserPasswordHasherInterface $passwordHasher,
        TokenRepository $tokenRepository,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    )
    {
        $this->doctrine = $doctrine;
        $this->tokenService = $tokenService;
        $this->bus = $bus;
        $this->passwordHasher = $passwordHasher;
        $this->tokenRepository = $tokenRepository;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    public function requestPasswordReset(string $email): JsonResponse
    {
        $em = $this->doctrine->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $tokenEntity = $this->tokenService->createToken($user);

        $this->bus->dispatch(new SendPasswordResetLinkMessage($email, $user->getUsername(), $tokenEntity->getTokenName()));
        return new JsonResponse(['message' => 'Email request have been sended successfully'], Response::HTTP_OK);
    }

    public function resetPassword(string $token, UpdatePasswordInput $UpdatePasswordInput): JsonResponse
    {
        $em = $this->doctrine->getManager();
        $tokenEntity = $em->getRepository(Token::class)->findOneBy(['token_name' => $token]);

        if (!$tokenEntity || $tokenEntity->isExpired()) {
            return new JsonResponse(['message' => 'Invalid or expired Token'], Response::HTTP_BAD_REQUEST);
        }

        $user = $tokenEntity->getUser();

        // Check if the last password change was within the last 30 days
        $now = new DateTime();
        if ($user->getLastPasswordChangedAt() !== null && $now->diff($user->getLastPasswordChangedAt())->days < 30) {
            return new JsonResponse(['message' => 'You cannot change your password more than once within 30 days'], Response::HTTP_BAD_REQUEST);
        }

        // Ensure new password is not the same as the current password
        if ($this->passwordEncoder->isPasswordValid($user, $UpdatePasswordInput->getNewPassword())) {
            return new JsonResponse(['message' => 'New password cannot be the same as the old password'], Response::HTTP_BAD_REQUEST);
        }
        if ($UpdatePasswordInput->getNewPassword() != $UpdatePasswordInput->getConfirmation()) {
            return new JsonResponse(['message' => 'New password and Confirmation do not match'], Response::HTTP_NOT_FOUND);
        }

        return $this->insert($tokenEntity, $UpdatePasswordInput->getNewPassword());
    }

    public function confirmPassword(string $token, ResetPasswordInput $resetPasswordInput): JsonResponse
    {
        $tokenEntity = $this->tokenRepository->findOneBy(['token_name' => $token]);
        if (!$tokenEntity) {
            return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_NOT_FOUND);
        }

        if ($tokenEntity->isExpired()) {
            return new JsonResponse(['message' => 'Invalid or expired token'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($resetPasswordInput);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }
            return new JsonResponse($errorMessages, Response::HTTP_BAD_REQUEST);
        }

        $user = $tokenEntity->getUser();

        // Check if the last password change was within the last 30 days
        

        // Check if the current password is valid
        if (!$this->passwordHasher->isPasswordValid($user, $resetPasswordInput->getCurrentPassword())) {
            return new JsonResponse(['message' => 'Invalid current password'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the new password and confirmation password match
        if ($resetPasswordInput->getNewPassword() !== $resetPasswordInput->getConfirmation()) {
            return new JsonResponse(['message' => 'New password and confirmation do not match'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the new password is the same as the current password
        if ($this->passwordHasher->isPasswordValid($user, $resetPasswordInput->getNewPassword())) {
            return new JsonResponse(['message' => 'New password cannot be the same as the current password'], Response::HTTP_BAD_REQUEST);
        }

        return $this->updatePassword($tokenEntity, $resetPasswordInput->getNewPassword());
    }

    private function updatePassword(Token $tokenEntity, string $newPassword): JsonResponse
    {
        $user = $tokenEntity->getUser();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $user->setLastPasswordChangedAt(new DateTime()); // Update the last password change date

        $this->entityManager->persist($user);

        $this->entityManager->flush();

//         Dispatch any messages if needed
        $this->bus->dispatch(new SendPasswordChangeConfirmationMessage($user->getEmail(), $user->getUsername()));

        return new JsonResponse(['message' => 'Password reset successfully'], Response::HTTP_OK);
    }
    public function insert(Token $tokenEntity, string $newPassword): JsonResponse
    {
        $em = $this->doctrine->getManager();
        $user = $tokenEntity->getUser();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);
        $user->setLastPasswordChangedAt(new DateTime()); // Update the last password change date
        $em->persist($user);
        $em->remove($tokenEntity);
        $em->flush();
        $this->bus->dispatch(new SendPasswordChangeConfirmationMessage($user->getEmail(), $user->getUsername()));
        return new JsonResponse(['message' => 'Password reset successfully'], Response::HTTP_OK);
    }
}
