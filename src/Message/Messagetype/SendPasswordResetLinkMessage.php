<?php

// src/Message/SendPasswordResetLinkMessage.php
namespace App\Message\Messagetype;

use App\Entity\Message;

class SendPasswordResetLinkMessage extends Message
{
    private string $resetToken;

    public function __construct(string $email, string $username, string $resetToken)
    {
        parent::__construct($email, $username);
        $this->resetToken = $resetToken;
    }

    public function getResetToken(): string
    {
        return $this->resetToken;
    }
}
