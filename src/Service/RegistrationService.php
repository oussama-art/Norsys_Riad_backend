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

        if ($this->userRepository->findOneBy(['email' => $userData['email']]) ||
            $this->userRepository->findOneBy(['username' => $userData['username']])) {
            return new JsonResponse(['message' => 'User with this email or username already exists'], 400);
        }

        $user = new User();
        $user->setEmail($userData['email']);
        $user->setUsername($userData['username']);
        $user->setRoles($userData['roles'] ?? []);
        $user->setPassword($userData['password']);

        $errors = $this->userService->validateUser($user);
        if (!empty($errors)) {
            return new JsonResponse($errors, 400);
        }

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $userData['password']
        );
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'User successfully registered'], 201);
    }
}
