<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;

class RegistrationService
{
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;
    private UserService $userService;

    public function __construct(
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher,
        UserService $userService,
        UserRepository $userRepository
    ) {
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    public function registerUser(array $userData): JsonResponse
    {
        $em = $this->doctrine->getManager();

        // Check if user already exists
        if ($this->userRepository->findOneBy(['email' => $userData['email']]) ||
            $this->userRepository->findOneBy(['username' => $userData['username']])) {
            return new JsonResponse(['message' => 'User with this email or username already exists'], 400);
        }

        $user = new User();
        $user->setEmail($userData['email']);
        $user->setUsername($userData['username']);
        $user->setFirstname($userData['firstname']);
        $user->setSecondname($userData['secondname']);
        $user->setCin($userData['cin']);
        $user->setAddress($userData['address']);
        $user->setTele($userData['tele']);
        $user->setRoles($userData['roles'] ?? $user->getRoles());

        // Validate user data
        if ($userData['password'] != $userData['passwordConfirmation']) {
            return new JsonResponse(['message' => 'Passwords do not match'], 400);
        }

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $userData['password']
        );
        $user->setPassword($hashedPassword);

        $errors = $this->userService->validateUser($user);
        if (!empty($errors)) {
            return new JsonResponse($errors, 400);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'User successfully registered'], 201);
    }

    public function updateUser(int $userId, array $userData): JsonResponse
    {
        $em = $this->doctrine->getManager();

        // Find the user by ID
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        // Update user details
        $user->setEmail($userData['email']);
        $user->setUsername($userData['username']);
        $user->setFirstname($userData['firstname']);
        $user->setSecondname($userData['secondname']);
        $user->setCin($userData['cin']);
        $user->setAddress($userData['address']);
        $user->setTele($userData['tele']);
        $user->setRoles($userData['roles'] ?? $user->getRoles());


        // Hash the new password if provided
        if (!empty($userData['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $userData['password']
            );
            $user->setPassword($hashedPassword);
        }

        $errors = $this->userService->validateUser($user);
        if (!empty($errors)) {
            return new JsonResponse($errors, 400);
        }

        $em->flush();

        return new JsonResponse(['message' => 'User successfully updated'], 200);
    }
}
