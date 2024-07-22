<?php

namespace App\Service;

use App\Dto\ResetPasswordInput;
use App\Dto\UpdatePasswordInput;
use App\Entity\Token;
use App\Entity\User;
use App\Message\Messagetype\SendPasswordChangeConfirmationMessage;
use App\Message\Messagetype\SendPasswordResetLinkMessage;
use App\Repository\TokenRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordResetService
{
    private ManagerRegistry $doctrine;

    private TokenService $tokenService;
    private MessageBusInterface $bus;

    private TokenRepository $tokenRepository;

    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordEncoder;


    public function __construct(
        ManagerRegistry $doctrine,
        MessageBusInterface $bus, // Inject MessageBusInterface
        TokenService $tokenService ,// Inject TokenService
        UserPasswordHasherInterface $passwordHasher,
        UserService $userService,
        TokenRepository $tokenRepository,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordEncoder
    )
    {
        $this->doctrine = $doctrine;
        $this->tokenService = $tokenService; // Assign TokenService
        $this->bus = $bus; // Assign MessageBusInterface
        $this->passwordHasher = $passwordHasher;
        $this->tokenRepository = $tokenRepository;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
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

        if (!$tokenEntity ||$tokenEntity->isExpired()) {
            return new JsonResponse(['message' => 'Invalid or expired Token'], Response::HTTP_BAD_REQUEST);
        }


        $user = $tokenEntity->getUser();

        // Ensure new password is not the same as the current password
        if ($user && $this->passwordEncoder->isPasswordValid($user, $UpdatePasswordInput->getNewPassword())) {
            return new JsonResponse(['message' => 'New password cannot be the same as the old password'], Response::HTTP_BAD_REQUEST);
        }
        if($UpdatePasswordInput->getNewPassword() != $UpdatePasswordInput->getConfirmation()){
            return new JsonResponse(['message' => 'New password and Confirmation do not match'], Response::HTTP_NOT_FOUND);
        }


        return $this->insert($tokenEntity, $UpdatePasswordInput->getNewPassword());

    }

    public function confirmPassword(string $token,resetPasswordInput $resetPasswordInput): JsonResponse{

        $em = $this->doctrine->getManager();
        $tokenEntity = $em->getRepository(Token::class)->findOneBy(['token_name' => $token]);
        if (!$tokenEntity) {
            return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_NOT_FOUND);

        }
        $token = $this->tokenRepository->findOneBy(['token_name' => $token]);
        if (!$token || $token->isExpired()) {
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

        $user = $token->getUser();

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
        return $this->insert($tokenEntity, $resetPasswordInput->getNewPassword());
    }


        public function insert(Token $tokenEntity, string $newPassword): JsonResponse
        {
            $em = $this->doctrine->getManager();
            $user = $tokenEntity->getUser();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $user->setPassword($hashedPassword);
            $em->persist($user);
            $em->remove($tokenEntity);
            $em->flush();
            $this->bus->dispatch(new SendPasswordChangeConfirmationMessage($user->getEmail(), $user->getUsername()));
            return new JsonResponse(['message' => 'Password reset successfully'], Response::HTTP_OK);
        }
    }
