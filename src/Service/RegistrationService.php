<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegistrationService
{
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHarsher;
    private ValidatorInterface $validator;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHarsher, ValidatorInterface $validator)
    {
        $this->doctrine = $doctrine;
        $this->passwordHarsher = $passwordHarsher;
        $this->validator = $validator;
    }

    public function registerUser(string $email, string $plaintextPassword, string $username): JsonResponse
    {
        $em = $this->doctrine->getManager();

        $user = new User();
        $hashedPassword = $this->passwordHarsher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setUsername($username);

        // Validate the user entity
        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorsString = '';
            foreach ($errors as $error) {
                $errorsString .= $error->getMessage() . ' ';
            }
            return new JsonResponse(['message' => trim($errorsString)], 400);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'User successfully registered'], 201);
    }
}
