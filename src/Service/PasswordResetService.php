<?php

namespace App\Service;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Doctrine\ORM\EntityManagerInterface; // Add this import for EntityManagerInterface

class PasswordResetService
{
    private ManagerRegistry $doctrine;
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private JWTTokenManagerInterface $JWTManager;
    private EntityManagerInterface $entityManager; // Define EntityManagerInterface for managing entities

    public function __construct(ManagerRegistry $doctrine, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator, JWTTokenManagerInterface $JWTManager, EntityManagerInterface $entityManager)
    {
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->JWTManager = $JWTManager;
        $this->entityManager = $entityManager; // Inject EntityManagerInterface
    }

    public function requestPasswordReset(string $email): void
    {
        $em = $this->doctrine->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        // Generate a secure identifier (reset token)
        $resetToken = $this->JWTManager->create($user);

        // Save the reset token to the database
        $tokenEntity = new Token();
        $tokenEntity->setTokenName($resetToken);
        $tokenEntity->setUser($user);
        $this->entityManager->persist($tokenEntity); // Use entityManager to persist
        $this->entityManager->flush(); // Use entityManager to flush changes

        // Generate the password reset URL for your React frontend
        $resetUrl = $this->urlGenerator->generate('react_password_reset', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

        // Send the password reset email
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->html("<p>To reset your password, please visit the following link: <a href=\"$resetUrl\">Reset Password</a></p>");

        $this->mailer->send($email);
    }


}
