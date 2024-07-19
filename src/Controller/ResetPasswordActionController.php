<?php

// src/Controller/ResetPasswordAction.php

namespace App\Controller;

use App\Dto\ResetPasswordInput;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\TokenRepository; // Assuming you have a repository for tokens
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ResetPasswordActionController extends AbstractController
{
    private UserRepository $userRepository;
    private TokenRepository $verificationTokenRepository; // Repository for verification tokens
//    private UserPasswordEncoderInterface $passwordEncoder;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;

    public function __construct(
        UserRepository $userRepository,
        TokenRepository $verificationTokenRepository, // Add repository for tokens
//        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ) {
        $this->userRepository = $userRepository;
        $this->verificationTokenRepository = $verificationTokenRepository; // Add repository for tokens
//        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $resetPasswordInput = $this->serializer->deserialize($data, ResetPasswordInput::class, 'json');

        // Validate the input data
        $errors = $this->validator->validate($resetPasswordInput);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if (!$user || !$this->passwordEncoder->isPasswordValid($user, $resetPasswordInput->currentPassword)) {
            return $this->json(['message' => 'Invalid current password'], Response::HTTP_BAD_REQUEST);
        }

        // Validate the verification token
        $token = $this->verificationTokenRepository->findOneBy(['token' => $resetPasswordInput->verificationToken]);

        if (!$token || $token->isExpired()) { // Implement `isExpired` method in your token entity
            return $this->json(['message' => 'Invalid or expired verification token'], Response::HTTP_BAD_REQUEST);
        }

        // Encode and set the new password
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $resetPasswordInput->newPassword);
        $user->setPassword($encodedPassword);

        $this->userRepository->save($user);

        // Optionally, invalidate the token after use
        $this->verificationTokenRepository->remove($token);

        return $this->json(['message' => 'Password successfully reset'], Response::HTTP_OK);
    }
}
