<?php

namespace App\Message\MessageHandler;

use App\Message\Messagetype\SendPasswordResetLinkMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendPasswordResetLinkMessageHandler
{
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(SendPasswordResetLinkMessage $message):void
    {
        $resetUrl = $this->urlGenerator->generate('react_password_reset', ['token' => $message->getResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Send the password reset email
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($message->getEmail())
            ->subject('Your password reset request')
            ->html("<p>To reset your password, please visit the following link: <a href=\"$resetUrl\">Reset Password</a></p>");

        $this->mailer->send($email);
    }
}
