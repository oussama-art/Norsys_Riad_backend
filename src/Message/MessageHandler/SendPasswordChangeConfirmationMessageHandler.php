<?php

namespace App\Message\MessageHandler;

use App\Message\Messagetype\SendPasswordChangeConfirmationMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendPasswordChangeConfirmationMessageHandler
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(SendPasswordChangeConfirmationMessage $message): void
    {
        // Send the password change confirmation email
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($message->getEmail())
            ->subject('Your password has been changed')
            ->html("<p>Hello {$message->getUsername()},</p><p>Your password has been successfully changed.</p>");

        $this->mailer->send($email);
    }
}
